<?php

declare(strict_types=1);

namespace App\Modules\Resource\Actions;

use App\Models\Resource;
use Illuminate\Support\Facades\Cache;

final class DeleteResourceAction
{
    public function execute(Resource $resource): void
    {
        $language = $resource->language;
        $resource->delete();

        Cache::forget("resources_{$language}_*"); // Invalidate cache
    }
}
