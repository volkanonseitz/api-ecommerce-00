<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\SettingsService; // jika ada, atau langsung ambil Settings model
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\GetSingleProductResource;
use App\DTO\ProductData;
use App\Models\Settings;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Type;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

    /**
     * GET /products - List products with filtering
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $products = $this->productService->getProducts($request, $limit);
        $data = ProductResource::collection($products)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    /**
     * POST /products - Create new product
     */
    public function store(ProductCreateRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        
        if (!$this->productService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        
        $settings = Settings::first();
        $data = ProductData::fromRequest($request->validated());
        $product = $this->productService->createProduct($data, $settings);
        
        return new ProductResource($product->load(['type', 'shop']));
    }

    /**
     * GET /products/{slug} - Get single product detail
     */
    public function show(Request $request, string $slug)
    {
        try {
            $product = $this->productService->getProductDetail($request, $slug);
            return new GetSingleProductResource($product);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(config('notice.NOT_FOUND'));
        }
    }

    /**
     * PUT /products/{id} - Update product
     */
    public function update(ProductUpdateRequest $request, int $id)
    {
        $user = $request->user();
        $product = Product::findOrFail($id);
        
        if (!$this->productService->hasPermission($user, $product->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        
        $settings = Settings::first();
        $data = ProductData::fromRequest($request->validated());
        $updated = $this->productService->updateProduct($product, $data, $settings);
        
        return new ProductResource($updated->load(['type', 'shop', 'categories', 'tags']));
    }

    /**
     * DELETE /products/{id} - Delete product
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        $product = Product::findOrFail($id);
        
        if (!$this->productService->hasPermission($user, $product->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        
        $this->productService->deleteProduct($product);
        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * GET /products/related - Get related products by slug
     */
    public function relatedProducts(Request $request)
    {
        $limit = $request->limit ?? 10;
        $slug = $request->slug;
        $language = $request->language ?? config('shop.default_language', 'id');
        
        $products = $this->productService->getRelatedProducts($slug, $limit, $language);
        return ProductResource::collection($products);
    }

    /**
     * GET /products/best-selling - Best selling products
     */
    public function bestSellingProducts(Request $request)
    {
        $products = $this->productService->getBestSellingProducts($request);
        return ProductResource::collection($products);
    }

    /**
     * GET /products/popular - Popular products
     */
    public function popularProducts(Request $request)
    {
        $products = $this->productService->getPopularProducts($request);
        return ProductResource::collection($products);
    }

    /**
     * GET /products/drafted - Drafted products for vendor
     */
    public function draftedProducts(Request $request)
    {
        $products = $this->productService->getDraftedProducts($request);
        return ProductResource::collection($products);
    }

    /**
     * GET /products/low-stock - Products with low stock
     */
    public function productStock(Request $request)
    {
        $products = $this->productService->getProductStock($request);
        return ProductResource::collection($products);
    }

    /**
     * GET /products/wishlists - User's wishlist
     */
    public function myWishlists(Request $request)
    {
        $products = $this->productService->getMyWishlists($request);
        return ProductResource::collection($products);
    }

    /**
     * POST /products/rental-price - Calculate rental price
     */
    public function calculateRentalPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from' => 'required|date',
            'to' => 'required|date|after:from',
            'variation_id' => 'nullable|exists:variation_options,id',
            'quantity' => 'nullable|integer|min:1',
            'persons' => 'nullable|array',
            'persons.*' => 'exists:resources,id',
            'features' => 'nullable|array',
            'features.*' => 'exists:resources,id',
            'deposits' => 'nullable|array',
            'deposits.*' => 'exists:resources,id',
            'dropoff_location_id' => 'nullable|exists:resources,id',
            'pickup_location_id' => 'nullable|exists:resources,id',
        ]);
        
        $price = $this->productService->calculateRentalPrice($request);
        return response()->json($price);
    }

    /**
     * GET /products/export/{shop_id} - Export products to CSV
     */
    public function exportProducts(Request $request, int $shopId)
    {
        $user = $request->user();
        if (!$this->productService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        
        $products = Product::with(['categories', 'tags'])
            ->where('shop_id', $shopId)
            ->get();
        
        $filename = 'products-for-shop-id-' . $shopId . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($products) {
            $handle = fopen('php://output', 'w');
            // Headers
            $headers = [
                'name', 'slug', 'price', 'sale_price', 'type_id', 'shop_id', 
                'author_id', 'manufacturer_id', 'language', 'product_type', 
                'quantity', 'unit', 'is_digital', 'is_external', 'description', 
                'sku', 'image', 'gallery', 'video', 'status', 'height', 
                'length', 'width', 'in_stock', 'is_taxable', 'visibility'
            ];
            fputcsv($handle, $headers);
            
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
            fclose($handle);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * GET /products/export-variations/{shop_id} - Export variation options to CSV
     */
    public function exportVariableOptions(Request $request, int $shopId)
    {
        $user = $request->user();
        if (!$this->productService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        
        $productIds = Product::where('shop_id', $shopId)->pluck('id');
        $variations = Variation::whereIn('product_id', $productIds)->get();
        
        $filename = 'variable-options-' . Str::random(5) . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($variations) {
            $handle = fopen('php://output', 'w');
            $headers = ['product_id', 'sku', 'title', 'price', 'sale_price', 'quantity', 'options', 'image'];
            fputcsv($handle, $headers);
            
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
            fclose($handle);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * POST /products/import - Import products from CSV
     */
    public function importProducts(Request $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        
        if (!$this->productService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        
        if (!$request->hasFile('csv')) {
            throw new HttpException(422, 'CSV file is required');
        }
        
        $file = $request->file('csv');
        $path = $file->storeAs('csv-imports', 'products-' . $shopId . '-' . time() . '.csv', 'local');
        $csvData = $this->csvToArray(storage_path('app/' . $path));
        
        $settings = Settings::first();
        foreach ($csvData as $row) {
            if (empty($row['type_id'])) {
                throw new \Exception('Invalid CSV: type_id is required');
            }
            
            $data = ProductData::fromRequest($row);
            // Override shop_id
            $data = new ProductData(
                name: $data->name,
                slug: $data->slug,
                price: $data->price,
                sale_price: $data->sale_price,
                max_price: $data->max_price,
                min_price: $data->min_price,
                type_id: $data->type_id,
                shop_id: $shopId,
                author_id: $data->author_id,
                manufacturer_id: $data->manufacturer_id,
                language: $data->language,
                product_type: $data->product_type,
                quantity: $data->quantity,
                unit: $data->unit,
                is_digital: $data->is_digital,
                is_external: $data->is_external,
                external_product_url: $data->external_product_url,
                external_product_button_text: $data->external_product_button_text,
                description: $data->description,
                sku: $data->sku,
                image: is_string($data->image) ? json_decode($data->image, true) : $data->image,
                gallery: is_string($data->gallery) ? json_decode($data->gallery, true) : $data->gallery,
                video: is_string($data->video) ? json_decode($data->video, true) : $data->video,
                status: $data->status,
                height: $data->height,
                length: $data->length,
                width: $data->width,
                in_stock: $data->in_stock,
                is_taxable: $data->is_taxable,
                sold_quantity: $data->sold_quantity,
                visibility: $data->visibility,
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
                is_rental: $row['is_rental'] ?? false,
            );
            
            $this->productService->createProduct($data, $settings);
        }
        
        return response()->json(['message' => 'Products imported successfully']);
    }

    /**
     * Helper to convert CSV to array
     */
    private function csvToArray(string $filename, string $delimiter = ','): array
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return [];
        }
        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }
}