<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Product;
use App\Models\Settings;
use App\Models\Variation;
use App\Modules\Product\Actions\CreateProductAction;
use App\Modules\Product\Actions\DeleteProductAction;
use App\Modules\Product\Actions\UpdateProductAction;
use App\Modules\Product\DTO\ProductData;
use App\Modules\Product\Http\Requests\ProductCreateRequest;
use App\Modules\Product\Http\Requests\ProductUpdateRequest;
use App\Modules\Product\Http\Resources\GetSingleProductResource;
use App\Modules\Product\Http\Resources\ProductResource;
use App\Modules\Product\Services\ProductMetricService;
use App\Modules\Product\Services\ProductRentalService;
use App\Modules\Product\Services\ProductService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductController extends BaseController
{
    public function __construct(
        private ProductService $productService,
        private ProductMetricService $productMetricService,
        private ProductRentalService $productRentalService,
        private CreateProductAction $createProductAction,
        private UpdateProductAction $updateProductAction,
        private DeleteProductAction $deleteProductAction,
    ) {}

    public function index(Request $request)
    {
        $limit = (int) ($request->limit ?? 15);
        $cacheKey = 'products_'.md5($request->fullUrl());
        $products = Cache::remember($cacheKey, 300, function () use ($request, $limit) {
            return $this->productService->getProducts($request, $limit);
        });

        return $this->sendPaginated(
            $products,
            ProductResource::collection($products->getCollection()),
            'Daftar produk berhasil diambil.'
        );
    }

    public function store(ProductCreateRequest $request)
    {
        $this->authorize('create', [Product::class, $request->shop_id]);
        $settings = Settings::first();
        $data = ProductData::fromRequest($request->validated());
        $product = $this->createProductAction->execute($data, $settings);
        Cache::forget('products_*');

        return $this->sendSuccess(
            new ProductResource($product->load(['type', 'shop'])),
            'Product created',
            201
        );
    }

    public function show(Request $request, string $slug)
    {
        try {
            $cacheKey = 'product_detail_'.$slug.'_'.($request->language ?? 'id');
            $product = Cache::remember($cacheKey, 600, function () use ($request, $slug) {
                return $this->productService->getProductDetail($request, $slug);
            });

            return $this->sendSuccess(
                new GetSingleProductResource($product),
                'Product detail'
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Product not found', 404);
        }
    }

    public function update(ProductUpdateRequest $request, int $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);
        $settings = Settings::first();
        $data = ProductData::fromRequest($request->validated());
        $updated = $this->updateProductAction->execute($product, $data, $settings);
        Cache::forget('product_detail_'.$product->slug.'_*');
        Cache::forget('products_*');

        return $this->sendSuccess(
            new ProductResource($updated->load(['type', 'shop', 'categories', 'tags'])),
            'Product updated'
        );
    }

    public function destroy(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);
        $slug = $product->slug;
        $this->deleteProductAction->execute($product);
        Cache::forget('product_detail_'.$slug.'_*');
        Cache::forget('products_*');

        return $this->sendSuccess(null, 'Product deleted successfully');
    }

    public function relatedProducts(Request $request)
    {
        $limit = (int) ($request->limit ?? 10);
        $slug = $request->slug;
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = "related_products_{$slug}_{$language}_{$limit}";
        $products = Cache::remember($cacheKey, 600, function () use ($slug, $limit, $language) {
            $product = Product::where('slug', $slug)->where('language', $language)->firstOrFail();

            return $this->productService->getRelatedProducts($product, $limit, $language);
        });

        return $this->sendSuccess(
            ProductResource::collection($products),
            'Related products'
        );
    }

    public function bestSellingProducts(Request $request)
    {
        $cacheKey = 'best_selling_'.md5($request->fullUrl());
        $products = Cache::remember($cacheKey, 600, function () use ($request) {
            return $this->productMetricService->getBestSellingProducts($request);
        });

        return $this->sendSuccess(
            ProductResource::collection($products),
            'Best selling products'
        );
    }

    public function popularProducts(Request $request)
    {
        $cacheKey = 'popular_products_'.md5($request->fullUrl());
        $products = Cache::remember($cacheKey, 600, function () use ($request) {
            return $this->productMetricService->getPopularProducts($request);
        });

        return $this->sendSuccess(
            ProductResource::collection($products),
            'Popular products'
        );
    }

    public function draftedProducts(Request $request)
    {
        $this->authorize('viewDrafted', Product::class);
        $products = $this->productService->getDraftedProducts($request);

        return $this->sendSuccess(
            ProductResource::collection($products),
            'Drafted products'
        );
    }

    public function productStock(Request $request)
    {
        $this->authorize('viewStock', Product::class);
        $products = $this->productService->getLowStockProducts($request);

        return $this->sendSuccess(
            ProductResource::collection($products),
            'Low stock products'
        );
    }

    public function myWishlists(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }
        $products = $this->productService->getMyWishlists($request);

        return $this->sendSuccess(
            ProductResource::collection($products),
            'Wishlist products'
        );
    }

    public function calculateRentalPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from' => 'required|date',
            'to' => 'required|date|after:from',
            'variation_id' => 'nullable|exists:variation_options,id',
            'quantity' => 'nullable|integer|min:1',
            'pickup_location_id' => 'nullable|exists:resources,id',
        ]);
        $price = $this->productRentalService->calculateRentalPrice($request);

        return $this->sendSuccess($price, 'Rental price calculated');
    }

    public function exportProducts(Request $request, int $shopId)
    {
        $this->authorize('export', [Product::class, $shopId]);

        $filename = 'products-for-shop-id-'.$shopId.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($shopId) {
            $handle = fopen('php://output', 'w');
            $headers = [
                'name', 'slug', 'price', 'sale_price', 'type_id', 'shop_id',
                'author_id', 'manufacturer_id', 'language', 'product_type',
                'quantity', 'unit', 'is_digital', 'is_external', 'description',
                'sku', 'image', 'gallery', 'video', 'status', 'height',
                'length', 'width', 'in_stock', 'is_taxable', 'visibility',
            ];
            fputcsv($handle, $headers);

            Product::where('shop_id', $shopId)->chunk(100, function ($products) use ($handle) {
                foreach ($products as $product) {
                    $row = [
                        $product->name,
                        $product->slug,
                        $product->price,
                        $product->sale_price,
                        $product->type_id,
                        $product->shop_id,
                        $product->author_id,
                        $product->manufacturer_id,
                        $product->language,
                        $product->product_type,
                        $product->quantity,
                        $product->unit,
                        $product->is_digital ? '1' : '0',
                        $product->is_external ? '1' : '0',
                        $product->description,
                        $product->sku,
                        json_encode($product->image),
                        json_encode($product->gallery),
                        json_encode($product->video),
                        $product->status,
                        $product->height,
                        $product->length,
                        $product->width,
                        $product->in_stock ? '1' : '0',
                        $product->is_taxable ? '1' : '0',
                        $product->visibility,
                    ];
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportVariableOptions(Request $request, int $shopId)
    {
        $this->authorize('export', [Product::class, $shopId]);

        $filename = 'variable-options-'.Str::random(5).'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($shopId) {
            $handle = fopen('php://output', 'w');
            $headers = ['product_id', 'sku', 'title', 'price', 'sale_price', 'quantity', 'options', 'image'];
            fputcsv($handle, $headers);

            $productIds = Product::where('shop_id', $shopId)->pluck('id');
            Variation::whereIn('product_id', $productIds)->chunk(100, function ($variations) use ($handle) {
                foreach ($variations as $variation) {
                    $row = [
                        $variation->product_id,
                        $variation->sku,
                        $variation->title,
                        $variation->price,
                        $variation->sale_price,
                        $variation->quantity,
                        json_encode($variation->options),
                        json_encode($variation->image),
                    ];
                    fputcsv($handle, $row);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importProducts(Request $request)
    {
        $this->authorize('import', [Product::class, $request->shop_id]);
        $shopId = $request->shop_id;

        if (! $request->hasFile('csv')) {
            return $this->sendError('CSV file is required', 422);
        }

        $file = $request->file('csv');
        $path = $file->storeAs('csv-imports', 'products-'.$shopId.'-'.time().'.csv', 'local');
        $csvData = $this->csvToArray(storage_path('app/'.$path));

        if (empty($csvData)) {
            return $this->sendError('CSV file is empty or invalid', 422);
        }

        $settings = Settings::first();
        $total = count($csvData);
        $success = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            try {
                if (empty($row['type_id'])) {
                    throw new \Exception('type_id is required at row '.($index + 1));
                }

                // Build ProductData from array
                $data = new ProductData(
                    name: $row['name'] ?? '',
                    slug: $row['slug'] ?? Str::slug($row['name'] ?? 'product-'.uniqid()),
                    price: (float) ($row['price'] ?? 0),
                    sale_price: isset($row['sale_price']) ? (float) $row['sale_price'] : null,
                    max_price: isset($row['max_price']) ? (float) $row['max_price'] : null,
                    min_price: isset($row['min_price']) ? (float) $row['min_price'] : null,
                    type_id: (int) $row['type_id'],
                    shop_id: $shopId,
                    author_id: isset($row['author_id']) ? (int) $row['author_id'] : null,
                    manufacturer_id: isset($row['manufacturer_id']) ? (int) $row['manufacturer_id'] : null,
                    language: $row['language'] ?? config('shop.default_language', 'id'),
                    product_type: $row['product_type'] ?? 'simple',
                    quantity: isset($row['quantity']) ? (int) $row['quantity'] : null,
                    unit: $row['unit'] ?? null,
                    is_digital: isset($row['is_digital']) ? (bool) $row['is_digital'] : false,
                    is_external: isset($row['is_external']) ? (bool) $row['is_external'] : false,
                    external_product_url: $row['external_product_url'] ?? null,
                    external_product_button_text: $row['external_product_button_text'] ?? null,
                    description: $row['description'] ?? null,
                    sku: $row['sku'] ?? null,
                    image: isset($row['image']) ? json_decode($row['image'], true) : null,
                    gallery: isset($row['gallery']) ? json_decode($row['gallery'], true) : null,
                    video: isset($row['video']) ? json_decode($row['video'], true) : null,
                    status: $row['status'] ?? 'draft',
                    height: isset($row['height']) ? (float) $row['height'] : null,
                    length: isset($row['length']) ? (float) $row['length'] : null,
                    width: isset($row['width']) ? (float) $row['width'] : null,
                    in_stock: isset($row['in_stock']) ? (bool) $row['in_stock'] : true,
                    is_taxable: isset($row['is_taxable']) ? (bool) $row['is_taxable'] : true,
                    sold_quantity: isset($row['sold_quantity']) ? (int) $row['sold_quantity'] : 0,
                    visibility: $row['visibility'] ?? 'public',
                    categories: isset($row['categories']) ? json_decode($row['categories'], true) : null,
                    tags: isset($row['tags']) ? json_decode($row['tags'], true) : null,
                    dropoff_locations: null,
                    pickup_locations: null,
                    persons: null,
                    features: null,
                    deposits: null,
                    metas: null,
                    variations: null,
                    variation_options: null,
                    digital_file: null,
                    inform_purchased_customer: false,
                    product_update_message: null,
                    is_rental: isset($row['is_rental']) ? (bool) $row['is_rental'] : false,
                );

                $this->createProductAction->execute($data, $settings);
                $success++;
            } catch (\Exception $e) {
                $errors[] = 'Row '.($index + 1).': '.$e->getMessage();
            }
        }

        Cache::forget('products_*');

        return $this->sendSuccess([
            'total' => $total,
            'success' => $success,
            'errors' => $errors,
        ], 'Import completed');
    }

    /**
     * Helper to convert CSV to array
     */
    private function csvToArray(string $filename, string $delimiter = ','): array
    {
        if (! file_exists($filename) || ! is_readable($filename)) {
            return [];
        }
        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (! $header) {
                    $header = $row;
                } else {
                    if (count($header) === count($row)) {
                        $data[] = array_combine($header, $row);
                    }
                }
            }
            fclose($handle);
        }

        return $data;
    }
}
