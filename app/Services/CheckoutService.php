<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CheckoutVerifyData;
use App\Models\Product;
use App\Models\Settings;
use App\Models\Shipping;
use App\Models\Tax;
use App\Models\User;
use App\Models\Variation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;

class CheckoutService
{
    public function __construct(private WalletService $walletService) {}

    public function checkStock(array $products): array
    {
        $productIds = array_column($products, 'product_id');
        $variationIds = array_filter(array_column($products, 'variation_option_id'));

        $productsById = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $variationsById = ! empty($variationIds)
            ? Variation::whereIn('id', $variationIds)->get()->keyBy('id')
            : collect();

        $unavailable = [];
        foreach ($products as $product) {
            $isUnavailable = false;
            if (isset($product['variation_option_id'])) {
                $variation = $variationsById->get($product['variation_option_id']);
                if (! $variation || $product['order_quantity'] > $variation->quantity) {
                    $isUnavailable = true;
                }
            } else {
                $productModel = $productsById->get($product['product_id']);
                if (! $productModel || $product['order_quantity'] > $productModel->quantity) {
                    $isUnavailable = true;
                }
            }
            if ($isUnavailable) {
                $unavailable[] = $product['product_id'];
            }
        }

        return $unavailable;
    }

    public function getOrderAmount(array $products, array $unavailableProducts): float
    {
        if (empty($unavailableProducts)) {
            return array_sum(array_column($products, 'subtotal'));
        }
        $amount = 0;
        foreach ($products as $product) {
            if (! in_array($product['product_id'], $unavailableProducts)) {
                $amount += $product['subtotal'];
            }
        }

        return $amount;
    }

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

        return $this->calculateShippingChargeByProduct($products);
    }

    private function calculateShippingChargeByProduct(array $products): float
    {
        $productIds = array_column($products, 'product_id');
        $productSubtotals = array_column($products, 'subtotal', 'product_id');
        $products = Product::with('shipping')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $total = 0;
        foreach ($products as $productId => $product) {
            $subtotal = $productSubtotals[$productId] ?? 0;
            if ($product->shipping) {
                $total += $this->getShippingCharge($product->shipping, $subtotal);
            }
        }

        return $total;
    }

    private function getShippingCharge(Shipping $shipping, float $amount): float
    {
        return match ($shipping->type) {
            'fixed' => (float) $shipping->amount,
            'percentage' => ($shipping->amount * $amount) / 100,
            default => 0,
        };
    }

    public function calculateTax(?array $billingAddress, ?array $shippingAddress, float $amount, float $shippingCharge): float
    {
        $taxClass = $this->getTaxClass($billingAddress, $shippingAddress);
        if (! $taxClass) {
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

        return null;
    }

    public function verify(CheckoutVerifyData $data, ?User $authUser): array
    {
        $user = $authUser;
        if (! $user) {
            throw new AuthorizationException('Authentication required');
        }

        $wallet = $user->wallet;

        $settings = Settings::getData();
        $minimumOrderAmount = $settings->options['minimumOrderAmount'] ?? 0;

        $unavailableProducts = $this->checkStock($data->products);
        $amount = $this->getOrderAmount($data->products, $unavailableProducts);

        $isFreeShippingEnabled = $settings->options['freeShipping'] ?? false;
        $freeShippingAmount = $settings->options['freeShippingAmount'] ?? 0;
        $shippingCharge = ($isFreeShippingEnabled && $freeShippingAmount <= $amount)
            ? 0
            : $this->calculateShippingCharge($data->products, $amount);

        $tax = $this->calculateTax($data->billing_address, $data->shipping_address, $amount, $shippingCharge);

        $total = $amount + $tax + $shippingCharge;

        if ($total < $minimumOrderAmount) {
            throw new \Exception('Minimum order amount is '.$minimumOrderAmount);
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
