<?php

declare(strict_types=1);

namespace App\Modules\AbusiveReport\Actions;

use App\Models\AbusiveReport;
use App\Modules\AbusiveReport\Enums\AbusiveReportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AcceptReportAction
{
    /**
     * @param  string  $modelType  (value dari enum, misal 'Review')
     */
    public function execute(string $modelType, int $modelId): void
    {
        DB::transaction(function () use ($modelType, $modelId) {
            $modelClass = AbusiveReportType::resolve($modelType)->modelClass();
            /** @var Model $model */
            $model = $modelClass::findOrFail($modelId);
            $model->delete();

            AbusiveReport::query()
                ->where('model_id', $modelId)
                ->where('model_type', $modelClass)
                ->delete();
        });
    }
}
