<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Product;
use App\Modules\Product\Http\Requests\ProductRentalRequest;
use App\Modules\Product\Services\ProductRentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductRentalController extends BaseController
{
    public function __construct(
        private ProductRentalService $rentalService
    ) {}

    public function calculateRentalPrice(ProductRentalRequest $request): JsonResponse
    {
        $this->authorize('manageRental', Product::class);
        
        $product = Product::findOrFail($request->product_id);
        $this->authorize('manageRental', $product);

        try {
            $pricing = $this->rentalService->calculateRentalPrice($request);

            return $this->sendSuccess($pricing, 'Rental price calculated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError(
                'Validation failed',
                422,
                $e->errors()
            );
        }
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after:from',
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->get('product_id');
        $from = $request->get('from');
        $to = $request->get('to');

        $unavailable = $this->rentalService->getUnavailableProductIds($from, $to);
        $isAvailable = !in_array((int) $productId, $unavailable);

        return $this->sendSuccess([
            'is_available' => $isAvailable,
            'product_id' => $productId,
            'from' => $from,
            'to' => $to,
        ], 'Availability checked successfully');
    }

    public function getBlockedDates(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $this->authorize('view', $product);

        $from = now()->toDateString();
        $to = now()->addYear()->toDateString();

        $unavailable = $this->rentalService->getUnavailableProductIds($from, $to);

        return $this->sendSuccess([
            'product_id' => $productId,
            'blocked_dates' => $unavailable,
            'from' => $from,
            'to' => $to,
        ], 'Blocked dates retrieved successfully');
    }
}