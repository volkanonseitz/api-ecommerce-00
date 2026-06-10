<?php

namespace App\Services;

use App\Models\AbusiveReport;
use App\DTO\AbusiveReportData;

class AbusiveReportService
{
    public function getReports(int $perPage = 15)
    {
        return AbusiveReport::paginate($perPage);
    }

    public function findOrFail(int $id): AbusiveReport
    {
        return AbusiveReport::findOrFail($id);
    }

    public function createReport(AbusiveReportData $data): AbusiveReport
    {
        $modelClass = $data->getModelClass();
        $model = $modelClass::findOrFail($data->model_id);

        // Cek apakah user sudah melaporkan model ini sebelumnya
        $existing = AbusiveReport::where('model_id', $data->model_id)
            ->where('model_type', $modelClass)
            ->where('user_id', $data->user_id)
            ->exists();
        if ($existing) {
            throw new \Exception(config('notice.YOU_HAVE_ALREADY_GIVEN_ABUSIVE_REPORT_FOR_THIS'));
        }

        return $model->abusive_reports()->create($data->toArray());
    }

    public function deleteReport(int $id): void
    {
        $report = AbusiveReport::findOrFail($id);
        $report->delete();
    }

    public function acceptReport(string $modelType, int $modelId): void
    {
        $modelClass = $this->resolveModelClass($modelType);
        $model = $modelClass::findOrFail($modelId);
        $model->delete();
    }

    public function rejectReport(string $modelType, int $modelId): void
    {
        $modelClass = $this->resolveModelClass($modelType);
        AbusiveReport::where('model_id', $modelId)
            ->where('model_type', $modelClass)
            ->delete();
    }

    public function getUserReports(int $userId, int $perPage = 15)
    {
        return AbusiveReport::where('user_id', $userId)->paginate($perPage);
    }

    private function resolveModelClass(string $type): string
    {
        $map = [
            'Review' => \App\Models\Review::class,
            'Question' => \App\Models\Question::class,
        ];
        return $map[$type] ?? 'App\\Models\\' . $type;
    }
}