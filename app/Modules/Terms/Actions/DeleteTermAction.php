<?php

declare(strict_types=1);

namespace App\Modules\Terms\Actions;

use App\Models\TermsAndConditions;
use Illuminate\Support\Facades\Cache;

final class DeleteTermAction
{
    public function execute(TermsAndConditions $term): void
    {
        $language = $term->language;
        $term->delete();

        Cache::forget("terms_{$language}_*"); // Invalidate cache
    }
}
