<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderedFile;
use App\Models\OrderWalletPoint;
use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\Settings;
use App\Models\User;
use App\Models\Variation;
use App\Modules\Order\DTO\OrderData;
use App\Modules\Order\Events\OrderProcessed;
use App\Modules\Order\Events\OrderReceived;
use App\Modules\Order\Services\OrderIdentityService;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Wallet\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateOrderAction
{
    public function __construct(
        private OrderIdentityService $identityService,
        private WalletService $walletService,
        private PaymentService $paymentService,
        private PersistOrderAction $persistOrderAction,
    ) {}

    public function execute(OrderData $data, Settings $settings, ?User $user = null): Order
    {
        return DB::transaction(function () use ($data, $settings, $user) {
            // 1. Sinkronisasi Data Identitas Dasar
            $data->tracking_number ??= $this->identityService->generateTrackingNumber();
            $data->customer_id ??= $user?->id;

            $data->order_status = $this->determineInitialOrderStatus($data->payment_gateway);
            $data->payment_status = $this->determineInitialPaymentStatus($data->payment_gateway);

            // 2. Hitung ulang semua nilai finansial dari database
            $calculated = $this->recalculateOrderAmounts($data);
            $data->amount = $calculated['amount'];
            $data->discount = $calculated['discount'];
            $data->sales_tax = $calculated['sales_tax'];
            $data->delivery_fee = $calculated['delivery_fee'];
            $data->paid_total = $calculated['paid_total'];
            $data->total = $calculated['total'];

            // 3. Simpan sementara poin yang akan digunakan (jika ada)
            $pointsToDeduct = 0;

            // 4. Interaksi Sistem Wallet Poin (sebelum order dibuat)
            if ($data->use_wallet_points && $user && $user->wallet) {
                $wallet = $user->wallet;
                $walletCurrency = $this->walletService->walletPointsToCurrency($wallet->available_points);
                $remainingTotal = $data->paid_total;

                if ($walletCurrency >= $remainingTotal) {
                    // Wallet cukup untuk bayar semua
                    $pointsToDeduct = $this->walletService->currencyToWalletPoints($remainingTotal);
                    $data->payment_gateway = PaymentGatewayType::FULL_WALLET_PAYMENT->value;
                    $data->order_status = OrderStatus::COMPLETED->value;
                    $data->payment_status = PaymentStatus::SUCCESS->value;
                    $data->paid_total = $data->total; // total tetap utuh, payment status jadi success
                } else {
                    // Wallet hanya sebagian
                    $pointsToDeduct = $wallet->available_points;
                    $data->paid_total = $remainingTotal - $walletCurrency;
                }
            }

            // 5. Menyimpan Parent Order melalui PersistOrderAction
            $order = $this->persistOrderAction->execute($data);

            // 6. Jika ada poin yang digunakan, lakukan pengurangan dan catat transaksi
            if ($pointsToDeduct > 0) {
                $this->walletService->deductPoints($user->id, $pointsToDeduct);
                OrderWalletPoint::create([
                    'amount' => $pointsToDeduct,
                    'order_id' => $order->id,
                ]);
            }

            // 7. Attach produk dan buat child orders
            if ($data->products) {
                $this->attachProducts($order, $data->products);
                $this->createChildOrders($order, $data);
            }

            // 8. Inisiasi Payment Gateway Intent Eksternal
            if (! in_array($order->payment_gateway, [
                PaymentGatewayType::CASH->value,
                PaymentGatewayType::CASH_ON_DELIVERY->value,
                PaymentGatewayType::FULL_WALLET_PAYMENT->value,
            ], true)) {
                $intent = $this->paymentService->createPaymentIntent($order, $settings, $order->payment_gateway);
                PaymentIntent::create([
                    'order_id' => $order->id,
                    'tracking_number' => $order->tracking_number,
                    'payment_gateway' => ucfirst($order->payment_gateway),
                    'payment_intent_info' => $intent,
                ]);
            }

            event(new OrderProcessed($order));

            return $order;
        });
    }

    private function determineInitialOrderStatus(?string $gateway): string
    {
        if (in_array($gateway, [PaymentGatewayType::CASH_ON_DELIVERY->value, PaymentGatewayType::CASH->value], true)) {
            return OrderStatus::PROCESSING->value;
        }

        return OrderStatus::PENDING->value;
    }

    private function determineInitialPaymentStatus(?string $gateway): string
    {
        return match ($gateway) {
            PaymentGatewayType::CASH_ON_DELIVERY->value => PaymentStatus::CASH_ON_DELIVERY->value,
            PaymentGatewayType::CASH->value => PaymentStatus::CASH->value,
            PaymentGatewayType::FULL_WALLET_PAYMENT->value => PaymentStatus::SUCCESS->value,
            default => PaymentStatus::PENDING->value,
        };
    }

    private function calculateDiscount(?Coupon $coupon, float $amount): float
    {
        if (! $coupon) {
            return 0.0;
        }
        if ($coupon->type === 'percentage') {
            return ($coupon->amount / 100) * $amount;
        }

        return min((float) $coupon->amount, $amount);
    }

    private function recalculateOrderAmounts(OrderData $data): array
    {
        $products = $data->products;
        if (empty($products)) {
            throw new BadRequestHttpException('Products required for order calculation.');
        }

        $amount = 0.0;
        $calculatedProducts = [];
        $productIds = array_column($products, 'product_id');
        $variationIds = array_filter(array_column($products, 'variation_option_id'));

        $productModels = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $variationModels = ! empty($variationIds)
            ? Variation::whereIn('id', $variationIds)->get()->keyBy('id')
            : collect();

        foreach ($products as $item) {
            $productId = $item['product_id'];
            $variationId = $item['variation_option_id'] ?? null;
            $quantity = $item['order_quantity'] ?? 0;

            if ($quantity <= 0) {
                throw new BadRequestHttpException('Invalid order quantity for product: '.$productId);
            }

            $product = $productModels->get($productId);
            if (! $product) {
                throw new BadRequestHttpException('Product not found: '.$productId);
            }

            // Hitung unit_price dari database
            $unitPrice = (float) ($product->sale_price ?? $product->price);

            if ($variationId) {
                $variation = $variationModels->get($variationId);
                if (! $variation || $variation->product_id !== $productId) {
                    throw new BadRequestHttpException('Variation not found for product: '.$productId);
                }
                $unitPrice = (float) ($variation->sale_price ?? $variation->price);
            }

            $subtotal = $unitPrice * $quantity;
            $amount += $subtotal;

            $calculatedProducts[] = [
                'product_id' => $productId,
                'variation_option_id' => $variationId,
                'order_quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        // Update $data->products dengan harga dari server
        $data->products = $calculatedProducts;

        // Hitung diskon
        $discount = 0.0;
        if ($data->coupon_id) {
            $coupon = Coupon::find($data->coupon_id);
            if ($coupon && $coupon->type === CouponType::FREE_SHIPPING_COUPON->value) {
                $data->delivery_fee = 0.0;
            }
            $discount = $this->calculateDiscount($coupon, $amount);
        }

        // Hitung ongkir (sederhana — bisa diganti dengan logic dari VerifyCheckoutAction)
        $deliveryFee = (float) ($data->delivery_fee ?? 0.0);

        // Hitung pajak (sederhana — bisa diganti dengan logic dari VerifyCheckoutAction)
        $salesTax = (float) ($data->sales_tax ?? 0.0);

        $paidTotal = $amount + $salesTax + $deliveryFee - $discount;
        if ($paidTotal < 0) {
            $paidTotal = 0.0;
        }

        return [
            'amount' => $amount,
            'discount' => $discount,
            'sales_tax' => $salesTax,
            'delivery_fee' => $deliveryFee,
            'paid_total' => $paidTotal,
            'total' => $paidTotal,
        ];
    }

    private function attachProducts(Order $order, array $products): void
    {
        $order->products()->attach($products);
        foreach ($products as $cartProduct) {
            $productModel = Product::find($cartProduct['product_id']);
            if ($productModel) {
                $this->handleDigitalFiles($cartProduct, $order, $productModel);
                $this->handleRentalProduct($cartProduct, $order, $productModel);
            }
        }
    }

    private function handleDigitalFiles(array $product, Order $order, Product $productModel): void
    {
        if (! $productModel->is_digital) {
            return;
        }
        $digitalFile = $productModel->digital_file;
        if (! $digitalFile) {
            return;
        }

        for ($i = 0; $i < $product['order_quantity']; $i++) {
            OrderedFile::create([
                'purchase_key' => Str::random(16),
                'digital_file_id' => $digitalFile->id,
                'customer_id' => $order->customer_id,
                'tracking_number' => $order->tracking_number,
            ]);
        }
    }

    private function handleRentalProduct(array $product, Order $order, Product $productModel): void
    {
        if (! $productModel->is_rental) {
            return;
        }
        $availabilityData = [
            'from' => Carbon::parse($product['from']),
            'to' => Carbon::parse($product['to']),
            'order_quantity' => $product['order_quantity'],
            'order_id' => $order->id,
            'language' => $order->language,
        ];

        if (isset($product['variation_option_id'])) {
            $variation = Variation::find($product['variation_option_id']);
            if ($variation) {
                $variation->availabilities()->create($availabilityData);
            }
        } else {
            $productModel->availabilities()->create($availabilityData);
        }
    }

    private function createChildOrders(Order $parentOrder, OrderData $data): void
    {
        $productsByShop = [];
        $productIds = array_column($data->products, 'product_id');
        $productModels = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($data->products as $cartProduct) {
            $product = $productModels->get($cartProduct['product_id']);
            if ($product) {
                $productsByShop[$product->shop_id][] = $cartProduct;
            }
        }

        foreach ($productsByShop as $shopId => $cartProducts) {
            $amount = array_sum(array_column($cartProducts, 'subtotal'));
            $childData = new OrderData(
                tracking_number: $this->identityService->generateTrackingNumber(),
                customer_id: $parentOrder->customer_id,
                shop_id: $shopId,
                language: $parentOrder->language,
                order_status: $parentOrder->order_status,
                payment_status: $parentOrder->payment_status,
                amount: $amount,
                sales_tax: 0.0,
                paid_total: $amount,
                total: $amount,
                delivery_time: $parentOrder->delivery_time,
                payment_gateway: $parentOrder->payment_gateway,
                altered_payment_gateway: $parentOrder->altered_payment_gateway,
                discount: 0.0,
                coupon_id: null,
                logistics_provider: $parentOrder->logistics_provider,
                billing_address: $parentOrder->billing_address,
                shipping_address: $parentOrder->shipping_address,
                delivery_fee: 0.0,
                customer_contact: $parentOrder->customer_contact,
                customer_name: $parentOrder->customer_name,
                note: $parentOrder->note,
                parent_id: $parentOrder->id,
                products: $cartProducts,
                use_wallet_points: false,
                isFullWalletPayment: false,
            );

            $childOrder = resolve(self::class)->execute($childData);
            $this->attachProducts($childOrder, $cartProducts);
            event(new OrderReceived($childOrder));
        }
    }
}
