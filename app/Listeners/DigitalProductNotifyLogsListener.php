<?php

namespace App\Listeners;

use App\Events\DigitalProductUpdateEvent;
use App\Models\NotifyLogs;
use App\Models\User;
use App\Notifications\DigitalProductUpdated;
use App\Modules\User\Services\UserQueryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class DigitalProductNotifyLogsListener implements ShouldQueue
{
    public function __construct(
        private UserQueryService $userService
    ) {}

    public function handle(DigitalProductUpdateEvent $event)
    {
        $product = $event->product;
        $user = $event->user;
        $optionalData = $event->optionalData ?? [];

        // Ambil daftar customer yang pernah membeli produk digital ini
        $orderedFiles = DB::table('ordered_files')
            ->join('digital_files', 'ordered_files.digital_file_id', '=', 'digital_files.id')
            ->when($product->product_type === 'variable', function ($query) {
                return $query->join('variation_options', 'digital_files.fileable_id', '=', 'variation_options.id')
                    ->join('products', 'products.id', '=', 'variation_options.product_id');
            })
            ->when($product->product_type === 'simple', function ($query) {
                return $query->join('products', 'products.id', '=', 'digital_files.fileable_id');
            })
            ->select(
                'ordered_files.id',
                'ordered_files.customer_id',
                'ordered_files.purchase_key',
                'ordered_files.digital_file_id',
                'ordered_files.tracking_number'
            )
            ->where('products.id', $product->id)
            ->groupBy('ordered_files.id', 'ordered_files.customer_id', 'ordered_files.purchase_key', 'ordered_files.digital_file_id', 'ordered_files.tracking_number')
            ->get();

        foreach ($orderedFiles as $file) {
            // Buat notifikasi untuk customer
            NotifyLogs::create([
                'receiver' => $file->customer_id,
                'sender' => $user->id,
                'notify_type' => 'product_update',
                'notify_receiver_type' => 'customer',
                'is_read' => false,
                'notify_text' => $optionalData['update_message'] ?? 'Product has been updated.',
                'notify_tracker' => $product->id,
            ]);

            // Kirim email ke customer
            $customer = User::find($file->customer_id);
            if ($customer) {
                $customer->notify(new DigitalProductUpdated($customer, $product, $optionalData));
            }
        }
    }
}
