<?php

namespace App\Services;

use App\DTO\CommissionData;
use App\Models\Commission;

class CommissionService
{
    public function getAll()
    {
        return Commission::get();
    }

    public function storeCommissions(array $commissions, string $language): void
    {
        // Hapus semua komisi untuk language ini, lalu insert ulang (atau sync)
        Commission::where('language', $language)->delete();
        foreach ($commissions as $comm) {
            $data = CommissionData::fromArray($comm, $language);
            Commission::create([
                'min_balance' => $data->min_balance,
                'max_balance' => $data->max_balance,
                'commission' => $data->commission,
                'level' => $data->level,
                'sub_level' => $data->sub_level,
                'language' => $data->language,
            ]);
        }
    }
}
