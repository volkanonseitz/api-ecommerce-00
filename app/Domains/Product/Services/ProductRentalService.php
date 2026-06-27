<?php

declare(strict_types=1);

namespace App\Domains\Product\Services;

use App\Models\Availability;
use App\Models\Product;
use App\Models\Resource;
use App\Models\Variation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

class ProductRentalService
{
    /**
     * @return array<int, int>
     */
    public function getUnavailableProductIds(string $from, string $to): array
    {
        $availabilities = Availability::whereDate('from', '<=', $from)
            ->whereDate('to', '>=', $to)
            ->get()
            ->groupBy('product_id');

        $unavailable = [];
        foreach ($availabilities as $productId => $items) {
            if (! $this->isProductAvailable($from, $to, (int) $productId, $items)) {
                $unavailable[] = (int) $productId;
            }
        }

        return $unavailable;
    }

    public function isProductAvailable(string $from, string $to, int $productId, mixed $blockedDates, int $requestedQuantity = 1): bool
    {
        $product = Product::findOrFail($productId);
        $totalBooked = 0;

        foreach ($blockedDates as $bd) {
            $period = Period::make(
                $bd->from,
                $bd->to,
                Precision::DAY(),
                Boundaries::EXCLUDE_END()
            );

            $range = Period::make(
                $from,
                $to,
                Precision::DAY(),
                Boundaries::EXCLUDE_END()
            );

            if ($period->overlapsWith($range)) {
                $totalBooked += $bd->order_quantity ?? 0;
            }
        }

        return ($product->quantity - $totalBooked) >= $requestedQuantity;
    }

    /**
     * @return array<string, float>
     *
     * @throws ValidationException
     */
    public function calculateRentalPrice(Request $request): array
    {
        $product = Product::findOrFail($request->product_id);
        if (! $product->is_rental) {
            throw ValidationException::withMessages([
                'product_id' => [config('notice.NOT_A_RENTAL_PRODUCT')],
            ]);
        }

        $from = Carbon::parse($request->from);
        $to = Carbon::parse($request->to);
        $bookedDays = $from->diffInDays($to);
        $quantity = $request->quantity ?? 1;

        $persons = (array) ($request->persons ?? []);
        $features = (array) ($request->features ?? []);
        $deposits = (array) ($request->deposits ?? []);

        if ($request->filled('variation_id')) {
            $variation = Variation::findOrFail($request->variation_id);
            $basePrice = ($variation->sale_price ?: $variation->price) * $bookedDays * $quantity;
        } else {
            $basePrice = ($product->sale_price ?: $product->price) * $bookedDays * $quantity;
        }

        $personPrice = $this->sumResourcePrices($persons);
        $featurePrice = $this->sumResourcePrices($features);
        $depositPrice = $this->sumResourcePrices($deposits);
        $dropoffPrice = $request->filled('dropoff_location_id') ? $this->getResourcePrice((int) $request->dropoff_location_id) : 0.0;
        $pickupPrice = $request->filled('pickup_location_id') ? $this->getResourcePrice((int) $request->pickup_location_id) : 0.0;

        return [
            'totalPrice' => (float) ($basePrice + $personPrice + $depositPrice + $featurePrice + $dropoffPrice + $pickupPrice),
            'personPrice' => $personPrice,
            'depositPrice' => $depositPrice,
            'featurePrice' => $featurePrice,
            'dropoffLocationPrice' => $dropoffPrice,
            'pickupLocationPrice' => $pickupPrice,
        ];
    }

    /**
     * @param  array<int, int>  $resourceIds
     */
    private function sumResourcePrices(array $resourceIds): float
    {
        if (empty($resourceIds)) {
            return 0.0;
        }

        return (float) Resource::whereIn('id', $resourceIds)->sum('price');
    }

    private function getResourcePrice(int $id): float
    {
        $resource = Resource::find($id);

        return $resource ? (float) $resource->price : 0.0;
    }
}
