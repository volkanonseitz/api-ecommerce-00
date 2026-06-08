<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variation;
use App\Models\User;
use App\Models\Settings;
use App\Models\Tax;
use App\Models\Shipping;
use App\DTO\CheckoutVerifyData;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CheckoutService
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Cek stok untuk setiap produk dalam keranjang
     * @return array List product_id yang stok tidak mencukupi
     */
    public function checkStock(array $products): array
    {
        $unavailable = [];
        foreach ($products as $product) {
            $isUnavailable = false;
            if (isset($product['variation_option_id'])) {
                $variation = Variation::find($product['variation_option_id']);
                if (!$variation || $product['order_quantity'] > $variation->quantity) {
                    $isUnavailable = true;
                }
            } else {
                $productModel = Product::find($product['product_id']);
                if (!$productModel || $product['order_quantity'] > $productModel->quantity) {
                    $isUnavailable = true;
                }
            }
            if ($isUnavailable) {
                $unavailable[] = $product['product_id'];
            }
        }
        return $unavailable;
    }

    /**
     * Hitung total amount dari produk yang tersedia (tidak unavailable)
     */
    public function getOrderAmount(array $products, array $unavailableProducts): float
    {
        if (empty($unavailableProducts)) {
            // amount sudah diberikan dari request, tapi kita hitung ulang untuk konsistensi
            return array_sum(array_column($products, 'subtotal'));
        }
        $amount = 0;
        foreach ($products as $product) {
            if (!in_array($product['product_id'], $unavailableProducts)) {
                $amount += $product['subtotal'];
            }
        }
        return $amount;
    }

    /**
     * Hitung shipping charge
     */
    public function calculateShippingCharge(array $products, float $amount): float
    {
        $orderedProducts = $products;
        $physicalProductIds = Product::whereIn('id', Arr::pluck($orderedProducts, 'product_id'))
            ->where('is_digital', false)
            ->pluck('id')
            ->toArray();

        if (empty($physicalProductIds)) {
            return 0;
        }

        $settings = Settings::getData();
        $shippingClassId = $settings->options['shippingClass'] ?? null;

        if ($shippingClassId) {
            $shippingClass = Shipping::find($shippingClassId);
            if ($shippingClass) {
                return $this->getShippingCharge($shippingClass, $amount);
            }
        }

        // Jika tidak ada shipping class global, hitung per product
        return $this->calculateShippingChargeByProduct($products);
    }

    private function calculateShippingChargeByProduct(array $products): float
    {
        $total = 0;
        foreach ($products as $product) {
            $total += $this->calculateEachProductCharge($product['product_id'], $product['subtotal']);
        }
        return $total;
    }

    private function calculateEachProductCharge(int $productId, float $subtotal): float
    {
        $product = Product::with('shipping')->find($productId);
        if ($product && $product->shipping) {
            return $this->getShippingCharge($product->shipping, $subtotal);
        }
        return 0;
    }

    private function getShippingCharge(Shipping $shipping, float $amount): float
    {
        return match ($shipping->type) {
            'fixed' => (float) $shipping->amount,
            'percentage' => ($shipping->amount * $amount) / 100,
            default => 0,
        };
    }

    /**
     * Hitung tax
     */
    public function calculateTax(?array $billingAddress, ?array $shippingAddress, float $amount, float $shippingCharge): float
    {
        $taxClass = $this->getTaxClass($billingAddress, $shippingAddress);
        if (!$taxClass) {
            return 0;
        }
        return ($amount * $taxClass->rate) / 100;
    }

    private function getTaxClass(?array $billingAddress, ?array $shippingAddress): ?Tax
    {
        $settings = Settings::getData();
        $taxClassId = $settings->options['taxClass'] ?? null;

        if ($taxClassId) {
            return Tax::find($taxClassId);
        }

        // Fallback ke tax berdasarkan alamat (jika diperlukan)
        // Sesuai asli, hanya menggunakan global tax class
        return null;
    }

    /**
     * Verifikasi checkout: hitung tax, shipping, cek stok, wallet
     */
    public function verify(CheckoutVerifyData $data, ?\App\Models\User $authUser): array
    {
        // Tentukan user
        $user = null;
        if ($data->customer_id) {
            $user = User::find($data->customer_id);
            if (!$user) {
                throw new ModelNotFoundException(config('notice.NOT_FOUND'));
            }
        } elseif ($authUser) {
            $user = $authUser;
        }

        $wallet = $user?->wallet;

        $settings = Settings::getData();
        $minimumOrderAmount = $settings->options['minimumOrderAmount'] ?? 0;

        // Cek stok
        $unavailableProducts = $this->checkStock($data->products);

        // Hitung amount (jika ada unavailable, kurangi)
        $amount = $this->getOrderAmount($data->products, $unavailableProducts);

        // Hitung shipping (free shipping jika memenuhi syarat)
        $isFreeShippingEnabled = $settings->options['freeShipping'] ?? false;
        $freeShippingAmount = $settings->options['freeShippingAmount'] ?? 0;
        $shippingCharge = ($isFreeShippingEnabled && $freeShippingAmount <= $amount) ? 0 : $this->calculateShippingCharge($data->products, $amount);

        // Hitung tax
        $tax = $this->calculateTax($data->billing_address, $data->shipping_address, $amount, $shippingCharge);

        $total = $amount + $tax + $shippingCharge;

        if ($total < $minimumOrderAmount) {
            throw new \Exception('Minimum order amount is ' . $minimumOrderAmount);
        }

        $walletPoints = $wallet ? $wallet->available_points : 0;

        return [
            'total_tax' => $tax,
            'shipping_charge' => $shippingCharge,
            'unavailable_products' => $unavailableProducts,
            'wallet_amount' => $walletPoints,
            'wallet_currency' => $this->walletService->walletPointsToCurrency($walletPoints),
        ];
    }
}