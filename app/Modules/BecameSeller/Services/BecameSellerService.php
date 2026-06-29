<?php

namespace App\Modules\BecameSeller\Services;

use App\Models\BecameSeller;
use App\Modules\BecameSeller\DTO\BecameSellerData;

class BecameSellerService
{
    public function getData(string $language): array
    {
        return BecameSeller::getData($language)?->page_options ?? [];
    }

    public function storeOrUpdate(BecameSellerData $data): BecameSeller
    {
        $existing = BecameSeller::where('language', $data->language)->first();
        if ($existing) {
            $existing->update(['page_options' => $data->page_options]);

            return $existing->fresh();
        } else {
            return BecameSeller::create([
                'page_options' => $data->page_options,
                'language' => $data->language,
            ]);
        }
    }

    public function getFirst(): ?BecameSeller
    {
        return BecameSeller::first();
    }
}
