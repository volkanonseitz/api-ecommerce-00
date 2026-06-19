<?php

namespace App\Actions;

use App\DTO\FaqsData;
use App\Models\Faqs;

class CreateFaqsAction
{
    public function execute(FaqsData $data): Faqs
    {
        return Faqs::create($data->toArray());
    }
}
