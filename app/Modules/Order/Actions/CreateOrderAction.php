<?php

declare(strict_types=1);

namespace App\Modules\Order\Actions;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Events\OrderProcessed;
use App\Events\OrderReceived;
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
use App\Modules\Order\Services\OrderIdentityService;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Wallet\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            // 2. Kalkulasi Nilai Subtotal Produk
            if (! $data->amount && $data->products) {
                $data->amount = array_sum(array_column($data->products, 'subtotal'));
            }

            // 3. Evaluasi Kupon Potongan Harga
            if ($data->coupon_id) {
                $coupon = Coupon::find($data->coupon_id);
                if ($coupon && $coupon->type === CouponType::FREE_SHIPPING_COUPON->value) {
                    $data->delivery_fee = 0.0;
                }
                $data->discount = $this->calculateDiscount($coupon, (float) $data->amount);
            }

            // 4. Kalkulasi Total Tagihan Akhir
            if (! $data->paid_total) {
                $data->paid_total = ($data->amount ?? 0.0) + ($data->sales_tax ?? 0.0) + ($data->delivery_fee ?? 0.0) - ($data->discount ?? 0.0);
                $data->total = $data->paid_total;
            }

            // 5. Interaksi Sistem Wallet Poin
            if ($data->use_wallet_points && $user && $user->wallet) {
                $wallet = $user->wallet;
                $amountDue = $data->paid_total - $this->walletService->walletPointsToCurrency($wallet->available_points);
                if ($amountDue <= 0) {
                    $data->payment_gateway = PaymentGatewayType::FULL_WALLET_PAYMENT->value;
                    $data->order_status = OrderStatus::COMPLETED->value;
                    $data->payment_status = PaymentStatus::SUCCESS->value;
                    $data->paid_total = $data->total;
                } else {
                    $data->paid_total = $amountDue;
                }
            }

            // 6. Menyimpan Parent Order melalui PersistOrderAction
            $order = $this->persistOrderAction->execute($data);

            if ($data->products) {
                $this->attachProducts($order, $data->products);
                $this->createChildOrders($order, $data);
            }

            // 7. Pengurangan Poin Wallet Jika Berlaku
            if ($data->use_wallet_points && $user && $user->wallet) {
                $pointsUsed = $this->walletService->currencyToWalletPoints($data->paid_total);
                if ($pointsUsed > 0) {
                    $this->walletService->deductPoints($user->id, $pointsUsed);
                    OrderWalletPoint::create(['amount' => $pointsUsed, 'order_id' => $order->id]);
                }
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

    private function attachProducts(Order $order, array $products): void
    {
        $attachments = [];
        $productIds = array_column($products, 'product_id');
        $productModels = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($products as $cartProduct) {
            $pId = $cartProduct['product_id'];
            $attachments[$pId] = [
                'order_quantity' => $cartProduct['order_quantity'],
                'unit_price' => $cartProduct['unit_price'],
                'subtotal' => $cartProduct['subtotal'],
                'variation_option_id' => $cartProduct['variation_option_id'] ?? null,
            ];

            $productModel = $productModels->get($pId);
            if ($productModel) {
                $this->handleDigitalFiles($cartProduct, $order, $productModel);
                $this->handleRentalProduct($cartProduct, $order, $productModel);
            }
        }
        $order->products()->attach($attachments);
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
