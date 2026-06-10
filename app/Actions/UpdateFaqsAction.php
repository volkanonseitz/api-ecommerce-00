<?php

namespace App\Actions;

use App\Models\Faqs;
use App\DTO\FaqsData;

class UpdateFaqsAction
{
    public function execute(Faqs $faqs, FaqsData $data): Faqs
    {
        $faqs->update($data->toArray());
        return $faqs->fresh();
    }
}