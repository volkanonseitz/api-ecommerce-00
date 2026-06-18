<?php

namespace App\Services;

use App\DTO\AbusiveReportData;
use App\Models\AbusiveReport;
use App\Enums\AbusiveReportTypes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AbusiveReportService
{
    public function getReports(int $perPage = 15)
    {
        return AbusiveReport::query()
            ->with('user')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): AbusiveReport
    {
        return AbusiveReport::query()
            ->with('user')
            ->findOrFail($id);
    }

    public function createReport(AbusiveReportData $data): AbusiveReport
    {
        $modelClass = $data->getModelClass();

        $model = $modelClass::findOrFail($data->model_id);

        try {
            return $model->abusive_reports()->create(
                $data->toArray()
            );
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new \RuntimeException(
                    config('notice.YOU_HAVE_ALREADY_GIVEN_ABUSIVE_REPORT_FOR_THIS')
                );
            }

            throw $e;
        }
    }

    public function deleteReport(int $id): void
    {
        AbusiveReport::findOrFail($id)->delete();
    }

    public function acceptReport(string $modelType, int $modelId): void
    {
        DB::transaction(function () use ($modelType, $modelId) {

            $modelClass = AbusiveReportTypes::resolve($modelType);

            $model = $modelClass::findOrFail($modelId);

            $model->delete();

            AbusiveReport::query()
                ->where('model_id', $modelId)
                ->where('model_type', $modelClass)
                ->delete();
        });
    }

    public function rejectReport(string $modelType, int $modelId): void
    {
        DB::transaction(function () use ($modelType, $modelId) {

            $modelClass = AbusiveReportTypes::resolve($modelType);

            AbusiveReport::query()
                ->where('model_id', $modelId)
                ->where('model_type', $modelClass)
                ->delete();
        });
    }

    public function getUserReports(int $userId, int $perPage = 15)
    {
        return AbusiveReport::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->paginate($perPage);
    }
}