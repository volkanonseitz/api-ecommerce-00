<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variation;
use App\Models\Availability;
use App\Models\Type;
use App\Models\Wishlist;
use App\Models\Resource;
use App\DTO\ProductData;
use App\Actions\CreateProductAction;
use App\Actions\UpdateProductAction;
use App\Enums\Permission;
use App\Enums\ProductStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Period\Period;
use Spatie\Period\Boundaries;
use Spatie\Period\Precision;

class ProductService
{
    public function __construct(
        private CreateProductAction $createProduct,
        private UpdateProductAction $updateProduct,
    ) {}

    /**
     * Check if user has permission to manage products in a shop
     */
    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        $shop = \App\Models\Shop::find($shopId);
        if (!$shop || !$shop->is_active) {
            throw new \Exception(config('notice.SHOP_NOT_APPROVED'));
        }
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $shop->staffs->contains($user->id);
        }
        return false;
    }

    /**
     * Build base products query with filters
     */
    public function getProductsQuery(Request $request): Builder
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = Product::where('language', $language);

        // Filter by date range for rental
        if ($request->filled('date_range')) {
            [$from, $to] = explode('//', $request->date_range);
            $unavailableIds = $this->getUnavailableProductIds($from, $to);
            $query->whereNotIn('id', $unavailableIds);
        }

        // Apply flash sale filters
        if ($request->has('flash_sale_builder')) {
            $query = $this->applyFlashSaleFilters($request, $query);
        }

        return $query;
    }

    /**
     * Get products with pagination
     */
    public function getProducts(Request $request, int $perPage = 15)
    {
        return $this->getProductsQuery($request)->paginate($perPage);
    }

    /**
     * Get single product by slug or id
     */
    public function getProductByIdentifier(string $identifier, string $language): Product
    {
        if (is_numeric($identifier)) {
            return Product::where('id', $identifier)->firstOrFail();
        }
        return Product::where('slug', $identifier)->where('language', $language)->firstOrFail();
    }

    /**
     * Get product with relations for detail page
     */
    public function getProductDetail(Request $request, string $slug): Product
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $user = $request->user();
        $limit = $request->limit ?? 10;

        $product = Product::where('language', $language)
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        // Check permission for digital files
        if ($request->has('with') && (str_contains($request->with, 'digital_file') || str_contains($request->with, 'variation_options.digital_file'))) {
            if (!$this->hasPermission($user, $product->shop_id)) {
                throw new \Exception(config('notice.NOT_AUTHORIZED'));
            }
        }

        // Load related products
        $related = $this->getRelatedProducts($slug, $limit, $language);
        $product->setRelation('related_products', $related);

        return $product;
    }

    /**
     * Create new product
     */
    public function createProduct(ProductData $data, $settings): Product
    {
        return $this->createProduct->execute($data, $settings);
    }

    /**
     * Update existing product
     */
    public function updateProduct(Product $product, ProductData $data, $settings): Product
    {
        return $this->updateProduct->execute($product, $data, $settings);
    }

    /**
     * Delete product
     */
    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Get unavailable product IDs for date range
     */
    public function getUnavailableProductIds(string $from, string $to): array
    {
        $availabilities = Availability::whereDate('from', '<=', $from)
            ->whereDate('to', '>=', $to)
            ->get()
            ->groupBy('product_id');

        $unavailable = [];
        foreach ($availabilities as $productId => $items) {
            if (!$this->isProductAvailable($from, $to, $productId, $items)) {
                $unavailable[] = $productId;
            }
        }
        return $unavailable;
    }

    /**
     * Check if product is available for booking
     */
    public function isProductAvailable(string $from, string $to, int $productId, $blockedDates, int $requestedQuantity = 1): bool
    {
        $product = Product::findOrFail($productId);
        $totalBooked = 0;
        foreach ($blockedDates as $bd) {
            $period = Period::make($bd['from'], $bd['to'], Precision::DAY, Boundaries::EXCLUDE_END);
            $range = Period::make($from, $to, Precision::DAY, Boundaries::EXCLUDE_END);
            if ($period->overlapsWith($range)) {
                $totalBooked += $bd->order_quantity ?? 0;
            }
        }
        return ($product->quantity - $totalBooked) >= $requestedQuantity;
    }

    /**
     * Get best selling products
     */
    public function getBestSellingProducts(Request $request)
    {
        $limit = $request->limit ?? 10;
        $language = $request->language ?? config('shop.default_language', 'id');
        $range = $request->range ?? '';
        $typeId = $this->resolveTypeId($request, $language);

        $query = Product::leftJoin('order_product', 'order_product.product_id', 'products.id')
            ->leftJoin('orders', 'order_product.order_id', '=', 'orders.id')
            ->selectRaw('products.*, sum(order_product.order_quantity) as total_sales')
            ->whereNull('orders.parent_id')
            ->where('orders.order_status', 'order-completed')
            ->where('orders.language', $language)
            ->groupBy('order_product.product_id')
            ->orderBy('total_sales', 'desc');

        if ($request->filled('shop_id')) {
            $query->where('products.shop_id', $request->shop_id);
        }
        if ($range) {
            $query->whereDate('products.created_at', '>', Carbon::now()->subDays((int)$range));
        }
        if ($typeId) {
            $query->where('products.type_id', $typeId);
        }

        return $query->take($limit)->get();
    }

    /**
     * Get related products
     */
    public function getRelatedProducts(string $slug, int $limit = 10, ?string $language = null): \Illuminate\Support\Collection
    {
        $language = $language ?? config('shop.default_language', 'id');
        $product = Product::where('slug', $slug)->firstOrFail();
        $categoryIds = $product->categories->pluck('id');
        
        if ($categoryIds->isEmpty()) {
            return collect();
        }
        
        return Product::where('language', $language)
            ->where('id', '!=', $product->id)
            ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds))
            ->with('type')
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular products (most ordered)
     */
    public function getPopularProducts(Request $request)
    {
        $limit = $request->limit ?? 10;
        $language = $request->language ?? config('shop.default_language', 'id');
        $range = $request->range ?? '';
        $typeId = $this->resolveTypeId($request, $language);

        $query = Product::withCount('orders')
            ->with(['type', 'shop'])
            ->orderBy('orders_count', 'desc')
            ->where('language', $language);

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }
        if ($range) {
            $query->whereDate('created_at', '>', Carbon::now()->subDays((int)$range));
        }
        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        return $query->take($limit)->get();
    }

    /**
     * Get top authors (for author controller, but keep here if needed)
     */
    // Not included, separate service

    /**
     * Get drafted products (for vendor dashboard)
     */
    public function getDraftedProducts(Request $request)
    {
        $user = $request->user();
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = Product::with(['type', 'shop'])->where('language', $language)->where('status', ProductStatus::DRAFT->value);

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            }
        } elseif ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            } else {
                $query->whereIn('shop_id', $user->shops->pluck('id'));
            }
        } elseif ($user->hasPermissionTo(Permission::STAFF->value)) {
            $query->where('shop_id', $user->managed_shop->id ?? null);
        } else {
            return $query->whereRaw('1 = 0'); // empty
        }

        return $query->paginate($request->limit ?? 15);
    }

    /**
     * Get products with low stock
     */
    public function getProductStock(Request $request)
    {
        $user = $request->user();
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = Product::with(['type', 'shop'])
            ->where('language', $language)
            ->where('quantity', '<', 10)
            ->where('quantity', '>', 0);

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            }
        } elseif ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            } else {
                $query->whereIn('shop_id', $user->shops->pluck('id'));
            }
        } elseif ($user->hasPermissionTo(Permission::STAFF->value)) {
            $query->where('shop_id', $user->managed_shop->id ?? null);
        } else {
            return $query->whereRaw('1 = 0');
        }

        return $query->paginate($request->limit ?? 15);
    }

    /**
     * Get user's wishlist products
     */
    public function getMyWishlists(Request $request)
    {
        $user = $request->user();
        $productIds = Wishlist::where('user_id', $user->id)->pluck('product_id');
        return Product::whereIn('id', $productIds)->paginate($request->limit ?? 10);
    }

    /**
     * Calculate rental price for a product
     */
    public function calculateRentalPrice(Request $request): array
    {
        $product = Product::findOrFail($request->product_id);
        if (!$product->is_rental) {
            throw new \Exception(config('notice.NOT_A_RENTAL_PRODUCT'));
        }

        $from = Carbon::parse($request->from);
        $to = Carbon::parse($request->to);
        $bookedDays = $from->diffInDays($to);
        $quantity = $request->quantity ?? 1;
        $persons = $request->persons ?? [];
        $features = $request->features ?? [];
        $deposits = $request->deposits ?? [];

        // Base price
        if ($request->filled('variation_id')) {
            $variation = Variation::findOrFail($request->variation_id);
            $basePrice = ($variation->sale_price ?: $variation->price) * $bookedDays * $quantity;
        } else {
            $basePrice = ($product->sale_price ?: $product->price) * $bookedDays * $quantity;
        }

        $personPrice = $this->sumResourcePrices($persons);
        $featurePrice = $this->sumResourcePrices($features);
        $depositPrice = $this->sumResourcePrices($deposits);
        $dropoffPrice = $request->filled('dropoff_location_id') ? $this->getResourcePrice($request->dropoff_location_id) : 0;
        $pickupPrice = $request->filled('pickup_location_id') ? $this->getResourcePrice($request->pickup_location_id) : 0;

        return [
            'totalPrice' => $basePrice + $personPrice + $depositPrice + $featurePrice + $dropoffPrice + $pickupPrice,
            'personPrice' => $personPrice,
            'depositPrice' => $depositPrice,
            'featurePrice' => $featurePrice,
            'dropoffLocationPrice' => $dropoffPrice,
            'pickupLocationPrice' => $pickupPrice,
        ];
    }

    private function sumResourcePrices(array $resourceIds): float
    {
        if (empty($resourceIds)) return 0;
        return Resource::whereIn('id', $resourceIds)->sum('price');
    }

    private function getResourcePrice(int $id): float
    {
        $resource = Resource::find($id);
        return $resource ? (float) $resource->price : 0;
    }

    private function resolveTypeId(Request $request, string $language): ?int
    {
        if ($request->filled('type_id')) {
            return (int) $request->type_id;
        }
        if ($request->filled('type_slug')) {
            $type = Type::where('slug', $request->type_slug)->where('language', $language)->first();
            return $type?->id;
        }
        return null;
    }

    private function applyFlashSaleFilters(Request $request, Builder $query): Builder
    {
        $user = $request->user();
        
        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            if ($request->searchedByUser === 'super_admin_builder') {
                $query->where('in_flash_sale', false)
                    ->whereNull('sale_price')
                    ->whereNotIn('id', fn($q) => $q->select('product_id')->from('flash_sale_requests_products'))
                    ->when($request->filled('shop_id'), fn($q) => $q->where('shop_id', $request->shop_id))
                    ->when($request->filled('author'), fn($q) => $q->where('author_id', $request->author))
                    ->when($request->filled('manufacturer'), fn($q) => $q->where('manufacturer_id', $request->manufacturer));
            } else {
                $query->where('in_flash_sale', true);
                if ($request->filled('shop_id')) {
                    $query->where('shop_id', $request->shop_id);
                }
            }
        } elseif ($user && $user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            if ($request->filled('shop_id')) {
                if ($request->searchedByUser === 'vendor') {
                    $query->where('in_flash_sale', false)
                        ->where('shop_id', $request->shop_id)
                        ->whereNull('sale_price');
                } else {
                    $query->where('in_flash_sale', true);
                }
            } else {
                $query->where('in_flash_sale', true)
                    ->whereIn('shop_id', $user->shops->pluck('id'));
            }
        } elseif ($user && $user->hasPermissionTo(Permission::STAFF->value)) {
            $query->where('in_flash_sale', true);
        } else {
            // Customer or guest
            $query->where('in_flash_sale', true);
        }
        
        return $query;
    }
}