<?php

namespace App\Listeners;

use App\Models\FlashSale; // Event ini tetap di App\Events sesuai rekomendasi
use App\Models\FlashSaleRequest; // Asumsi model FlashSale ada
use App\Models\Product; // Asumsi model FlashSaleRequest ada
use App\Modules\FlashSale\Events\FlashSaleProcessed; // Asumsi model Product ada
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class FlashSaleProductProcess implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FlashSaleProcessed $event): void
    {
        try {
            switch ($event->action_type) {
                case 'append_attached_products':
                    $this->appendAttachedProducts($event->data);
                    break;
                case 'remove_attached_products':
                    $this->removeAttachedProducts($event->data);
                    break;
                case 'delete_vendor_request':
                    $this->deleteVendorRequest($event->data);
                    break;
                default:
                    Log::warning('Unknown FlashSaleProcessed action type: '.$event->action_type);
                    break;
            }
        } catch (Throwable $e) {
            Log::error('Error processing FlashSaleProcessed event: '.$e->getMessage(), ['event' => $event]);
        }
    }

    protected function appendAttachedProducts(array $data): void
    {
        $products = Product::whereIn('id', $data['products'] ?? [])->get();
        if (! $products->isEmpty()) {
            foreach ($products as $product) {
                $flashSaleProduct = [
                    'flash_sale_id' => $data['flash_sale_id'],
                    'product_id' => $product->id,
                    'quantity' => $data['product_quantity'][$product->id] ?? $product->quantity,
                    'price' => $data['product_price'][$product->id] ?? $product->price,
                    'sale_price' => $data['sale_price'][$product->id] ?? $product->sale_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                // Asumsi ada tabel pivot flash_sale_products
                \DB::table('flash_sale_products')->insert($flashSaleProduct);
                Log::info("Product {$product->id} appended to Flash Sale {$data['flash_sale_id']}");
            }
        }
    }

    protected function removeAttachedProducts(array $data): void
    {
        // Logika untuk menghapus produk dari Flash Sale
        \DB::table('flash_sale_products')
            ->where('flash_sale_id', $data['flash_sale_id'])
            ->whereIn('product_id', $data['products'] ?? [])
            ->delete();
        Log::info("Products removed from Flash Sale {$data['flash_sale_id']}");

        // Juga hapus request dari FlashSaleRequest jika ada
        if (isset($data['flash_sale_request_id'])) {
            FlashSaleRequest::find($data['flash_sale_request_id'])?->delete();
            Log::info("Flash Sale Request {$data['flash_sale_request_id']} deleted.");
        }
    }

    protected function deleteVendorRequest(array $data): void
    {
        // Logika untuk menghapus request vendor dari FlashSaleRequest
        if (isset($data['flash_sale_request_id'])) {
            FlashSaleRequest::find($data['flash_sale_request_id'])?->delete();
            Log::info("Flash Sale Request {$data['flash_sale_request_id']} deleted.");
        }
    }
}
