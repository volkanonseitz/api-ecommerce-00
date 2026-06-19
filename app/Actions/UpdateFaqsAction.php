<?php

namespace App\Actions;

use App\DTO\FaqsData;
use App\Models\Faqs;

class UpdateFaqsAction
{
    public function execute(Faqs $faqs, FaqsData $data): Faqs
    {
        $faqs->update($data->toArray());

        return $faqs->fresh();
    }
}
