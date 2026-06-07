<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Coupon;
use App\Models\Wallet;
use App\Models\Balance;
use App\Models\OrderWalletPoint;
use App\Models\OrderedFile;
use App\Models\Settings;
use App\DTO\OrderData;
use App\Actions\CreateOrderAction;
use App\Enums\Permission;
use App\Enums\OrderStatus;
use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Enums\CouponType;
use App\Enums\ProductType;
use App\Events\OrderCreated;
use App\Events\OrderProcessed;
use App\Events\OrderReceived;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Auth\Access\AuthorizationException;

class OrderService
{
    public function __construct(
        private CreateOrderAction $createOrder,
        private WalletService $walletService,
        private PaymentService $paymentService,
    ) {}

    public function generateTrackingNumber(): string
    {
        $today = date('Ymd');
        do {
            $trackingNumber = $today . random_int(100000, 999999);
        } while (Order::where('tracking_number', $trackingNumber)->exists());
        return $trackingNumber;
    }

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;
        $shop = \App\Models\Shop::find($shopId);
        if (!$shop) return false;
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $shop->staffs->contains($user->id);
        }
        return false;
    }

    public function getOrdersQuery(Request $request, Authenticatable $user)
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return Order::with('children')->whereNull('parent_id');
        } elseif ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            if ($request->shop_id && $this->hasPermission($user, $request->shop_id)) {
                return Order::with('children')->where('shop_id', $request->shop_id)->whereNotNull('parent_id');
            } else {
                return Order::with('children')->whereNotNull('parent_id')->whereIn('shop_id', $user->shops->pluck('id'));
            }
        } elseif ($user->hasPermissionTo(Permission::STAFF->value)) {
            if ($request->shop_id && $this->hasPermission($user, $request->shop_id)) {
                return Order::with('children')->where('shop_id', $request->shop_id)->whereNotNull('parent_id');
            } else {
                return Order::with('children')->whereNotNull('parent_id')->where('shop_id', $user->shop_id);
            }
        } else {
            return Order::with('children')->where('customer_id', $user->id)->whereNull('parent_id');
        }
    }

    public function getOrderByTrackingOrId($param, string $language, ?Authenticatable $user = null): Order
    {
        $order = Order::where('language', $language)
            ->with(['products', 'shop', 'children.shop', 'wallet_point'])
            ->where(function($q) use ($param) {
                $q->where('id', $param)->orWhere('tracking_number', $param);
            })->firstOrFail();

        if ($order->customer_id && $user) {
            if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
                return $order;
            }
            if ($order->shop_id && $this->hasPermission($user, $order->shop_id)) {
                return $order;
            }
            if ($user->id == $order->customer_id) {
                return $order;
            }
        } elseif (!$order->customer_id) {
            return $order;
        }
        throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
    }

    public function createOrder(OrderData $data, Settings $settings, ?User $user = null): Order
    {
        return DB::transaction(function () use ($data, $settings, $user) {
            // Set tracking number jika belum
            if (!$data->tracking_number) {
                $data->tracking_number = $this->generateTrackingNumber();
            }
            // Set customer_id dari user jika tidak ada
            if (!$data->customer_id && $user) {
                $data->customer_id = $user->id;
            }
            // Set default order status & payment status berdasarkan payment gateway
            $data->order_status = $this->determineInitialOrderStatus($data->payment_gateway);
            $data->payment_status = $this->determineInitialPaymentStatus($data->payment_gateway);

            // Hitung subtotal jika tidak diberikan
            if (!$data->amount && $data->products) {
                $data->amount = $this->calculateSubtotal($data->products);
            }
            // Proses coupon jika ada
            if ($data->coupon_id) {
                $coupon = Coupon::find($data->coupon_id);
                if ($coupon && $coupon->type === CouponType::FREE_SHIPPING_COUPON) {
                    $data->delivery_fee = 0;
                }
                // Hitung discount
                $data->discount = $this->calculateDiscount($coupon, $data->amount);
            }
            // Hitung paid_total jika belum
            if (!$data->paid_total) {
                $data->paid_total = $data->amount + $data->sales_tax + $data->delivery_fee - $data->discount;
                $data->total = $data->paid_total;
            }

            // Handle wallet payment
            if ($data->use_wallet_points && $user && $user->wallet) {
                $wallet = $user->wallet;
                $amountDue = $data->paid_total - $this->walletService->walletPointsToCurrency($wallet->available_points);
                if ($amountDue <= 0) {
                    // Full wallet payment
                    $data->payment_gateway = PaymentGatewayType::FULL_WALLET_PAYMENT;
                    $data->order_status = OrderStatus::COMPLETED;
                    $data->payment_status = PaymentStatus::SUCCESS;
                    $data->paid_total = $data->total; // reset
                } else {
                    $data->paid_total = $amountDue;
                }
            }

            // Create parent order
            $order = $this->createOrder->execute($data);

            // Attach products
            $this->attachProducts($order, $data->products);

            // Create child orders per shop
            $this->createChildOrders($order, $data);

            // Handle wallet point deduction
            if ($data->use_wallet_points && $user && $user->wallet) {
                $pointsUsed = $this->walletService->currencyToWalletPoints($data->paid_total);
                if ($pointsUsed > 0) {
                    $this->walletService->deductPoints($user->id, $pointsUsed);
                    OrderWalletPoint::create(['amount' => $pointsUsed, 'order_id' => $order->id]);
                }
            }

            // Create payment intent if needed
            if (!in_array($order->payment_gateway, [
                PaymentGatewayType::CASH, PaymentGatewayType::CASH_ON_DELIVERY, PaymentGatewayType::FULL_WALLET_PAYMENT
            ])) {
                $intent = $this->paymentService->createPaymentIntent($order, $settings, $order->payment_gateway);
                // Simpan payment intent (misal ke PaymentIntent model)
                \App\Models\PaymentIntent::create([
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
        if (in_array($gateway, [PaymentGatewayType::CASH_ON_DELIVERY, PaymentGatewayType::CASH])) {
            return OrderStatus::PROCESSING;
        }
        return OrderStatus::PENDING;
    }

    private function determineInitialPaymentStatus(?string $gateway): string
    {
        return match($gateway) {
            PaymentGatewayType::CASH_ON_DELIVERY => PaymentStatus::CASH_ON_DELIVERY,
            PaymentGatewayType::CASH => PaymentStatus::CASH,
            PaymentGatewayType::FULL_WALLET_PAYMENT => PaymentStatus::SUCCESS,
            default => PaymentStatus::PENDING,
        };
    }

    private function calculateSubtotal(array $products): float
    {
        return array_sum(array_column($products, 'subtotal'));
    }

    private function calculateDiscount(?Coupon $coupon, float $amount): float
    {
        if (!$coupon) return 0;
        // Implementasi sederhana: jika coupon type percentage atau fixed
        if ($coupon->type === 'percentage') {
            return ($coupon->amount / 100) * $amount;
        }
        return min($coupon->amount, $amount);
    }

    private function attachProducts(Order $order, array $products): void
    {
        $attachments = [];
        foreach ($products as $product) {
            $attachments[$product['product_id']] = [
                'order_quantity' => $product['order_quantity'],
                'unit_price' => $product['unit_price'],
                'subtotal' => $product['subtotal'],
                'variation_option_id' => $product['variation_option_id'] ?? null,
            ];
            // Handle digital files & rental
            $this->handleDigitalFiles($product, $order);
            $this->handleRentalProduct($product, $order);
        }
        $order->products()->attach($attachments);
    }

    private function handleDigitalFiles(array $product, Order $order): void
    {
        $productModel = Product::find($product['product_id']);
        if (!$productModel || !$productModel->is_digital) return;
        $digitalFile = $productModel->digital_file;
        if (!$digitalFile) return;
        for ($i = 0; $i < $product['order_quantity']; $i++) {
            OrderedFile::create([
                'purchase_key' => Str::random(16),
                'digital_file_id' => $digitalFile->id,
                'customer_id' => $order->customer_id,
                'tracking_number' => $order->tracking_number,
            ]);
        }
    }

    private function handleRentalProduct(array $product, Order $order): void
    {
        $productModel = Product::find($product['product_id']);
        if (!$productModel || !$productModel->is_rental) return;
        $availabilityData = [
            'from' => Carbon::parse($product['from']),
            'to' => Carbon::parse($product['to']),
            'order_quantity' => $product['order_quantity'],
            'order_id' => $order->id,
            'language' => $order->language,
        ];
        if (isset($product['variation_option_id'])) {
            $variation = Variation::find($product['variation_option_id']);
            if ($variation) $variation->availabilities()->create($availabilityData);
        } else {
            $productModel->availabilities()->create($availabilityData);
        }
    }

    private function createChildOrders(Order $parentOrder, OrderData $data): void
    {
        $productsByShop = [];
        foreach ($data->products as $cartProduct) {
            $product = Product::find($cartProduct['product_id']);
            $productsByShop[$product->shop_id][] = $cartProduct;
        }
        foreach ($productsByShop as $shopId => $cartProducts) {
            $amount = array_sum(array_column($cartProducts, 'subtotal'));
            $childData = new OrderData(
                tracking_number: $this->generateTrackingNumber(),
                customer_id: $parentOrder->customer_id,
                shop_id: $shopId,
                language: $parentOrder->language,
                order_status: $parentOrder->order_status,
                payment_status: $parentOrder->payment_status,
                amount: $amount,
                sales_tax: 0,
                paid_total: $amount,
                total: $amount,
                delivery_time: $parentOrder->delivery_time,
                payment_gateway: $parentOrder->payment_gateway,
                altered_payment_gateway: $parentOrder->altered_payment_gateway,
                discount: 0,
                coupon_id: null,
                logistics_provider: $parentOrder->logistics_provider,
                billing_address: $parentOrder->billing_address,
                shipping_address: $parentOrder->shipping_address,
                delivery_fee: 0,
                customer_contact: $parentOrder->customer_contact,
                customer_name: $parentOrder->customer_name,
                note: $parentOrder->note,
                parent_id: $parentOrder->id,
                products: $cartProducts,
                use_wallet_points: false,
                isFullWalletPayment: false,
            );
            $childOrder = $this->createOrder->execute($childData);
            $this->attachProducts($childOrder, $cartProducts);
            event(new OrderReceived($childOrder));
        }
    }

    public function updateOrderStatus(Order $order, string $newStatus, Authenticatable $user): Order
    {
        if ($order->shop_id && !$this->hasPermission($user, $order->shop_id) && !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $oldStatus = $order->order_status;
        $order->order_status = $newStatus;
        $order->save();

        // Update child orders jika parent
        if ($order->parent_id === null) {
            foreach ($order->children as $child) {
                $child->order_status = $newStatus;
                $child->save();
            }
        }

        // Handle inventory jika status berubah ke cancelled/completed dll.
        if ($newStatus === OrderStatus::CANCELLED && $oldStatus !== OrderStatus::CANCELLED) {
            // Restore inventory
            $this->restoreProductInventory($order);
        }

        return $order;
    }

    private function restoreProductInventory(Order $order): void
    {
        foreach ($order->products as $product) {
            $quantity = $product->pivot->order_quantity;
            $product->increment('quantity', $quantity);
            if ($product->product_type === ProductType::VARIABLE && $product->pivot->variation_option_id) {
                $variation = Variation::find($product->pivot->variation_option_id);
                if ($variation) $variation->increment('quantity', $quantity);
            }
        }
    }

    // Method untuk export, invoice, submitPayment, dll.
    public function getExportToken(int $userId, ?int $shopId): string
    {
        $token = Str::random(16);
        \App\Models\DownloadToken::create([
            'user_id' => $userId,
            'token' => $token,
            'payload' => $shopId,
        ]);
        return route('export_order.token', ['token' => $token]);
    }

    public function getInvoiceToken(int $userId, int $orderId, string $language, array $translatedText, bool $isRtl): string
    {
        $payload = serialize([
            'user_id' => $userId,
            'order_id' => $orderId,
            'language' => $language,
            'translated_text' => $translatedText,
            'is_rtl' => $isRtl,
        ]);
        $token = Str::random(16);
        \App\Models\DownloadToken::create([
            'user_id' => $userId,
            'token' => $token,
            'payload' => $payload,
        ]);
        return route('download_invoice.token', ['token' => $token]);
    }
}