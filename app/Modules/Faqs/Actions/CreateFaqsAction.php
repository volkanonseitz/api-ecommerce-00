<?php

declare(strict_types=1);

namespace App\Modules\Faqs\Actions;

use App\Models\Faqs;
use App\Modules\Faqs\DTO\FaqsData;

class CreateFaqsAction
{
    public function execute(FaqsData $data): Faqs
    {
        return Faqs::create($data->toArray());
    }
}
