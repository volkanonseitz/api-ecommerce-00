<?php

declare(strict_types=1);

namespace App\Modules\Faqs\Actions;

use App\Models\Faqs;

final class DeleteFaqsAction
{
    public function execute(Faqs $faqs): void
    {
        $faqs->delete();
    }
}
