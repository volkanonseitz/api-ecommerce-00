<?php

namespace App\Actions;

use App\Models\Faqs;
use App\DTO\FaqsData;

class CreateFaqsAction
{
    public function execute(FaqsData $data): Faqs
    {
        return Faqs::create($data->toArray());
    }
}