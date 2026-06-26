<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\AbusiveReportData;
use App\Enums\AbusiveReportType;
use App\Models\AbusiveReport;
use App\Traits\AuthorizesShopAccess;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AbusiveReportService
{
    use AuthorizesShopAccess;

    public function getReports(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
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

    public function createReport(AbusiveReportData $data, Authenticatable $user): AbusiveReport
    {
        $modelClass = $data->getModelClass();
        $model = $modelClass::findOrFail($data->model_id);

        try {
            return $model->abusive_reports()->create($data->toArray());
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new \RuntimeException(config('notice.YOU_HAVE_ALREADY_GIVEN_ABUSIVE_REPORT_FOR_THIS'));
            }
            throw $e;
        }
    }

    public function deleteReport(int $id, Authenticatable $user): void
    {
        $report = AbusiveReport::findOrFail($id);
        if ($report->user_id !== $user->id && !$user->hasPermissionTo('super_admin')) {
            abort(403, config('notice.NOT_AUTHORIZED'));
        }
        $report->delete();
    }

    public function acceptReport(string $modelType, int $modelId): void
    {
        DB::transaction(function () use ($modelType, $modelId) {
            $modelClass = AbusiveReportType::resolve($modelType);
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
            $modelClass = AbusiveReportType::resolve($modelType);
            AbusiveReport::query()
                ->where('model_id', $modelId)
                ->where('model_type', $modelClass)
                ->delete();
        });
    }

    public function getUserReports(int $userId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return AbusiveReport::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->paginate($perPage);
    }
}