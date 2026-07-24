<?php

declare(strict_types=1);

namespace App\Modules\AbusiveReport\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\AbusiveReport;
use App\Modules\AbusiveReport\Actions\AcceptReportAction;
use App\Modules\AbusiveReport\Actions\CreateReportAction;
use App\Modules\AbusiveReport\Actions\DeleteReportAction;
use App\Modules\AbusiveReport\Actions\RejectReportAction;
use App\Modules\AbusiveReport\DTO\AbusiveReportData;
use App\Modules\AbusiveReport\Http\Requests\AcceptOrRejectAbusiveReportRequest;
use App\Modules\AbusiveReport\Http\Requests\CreateAbusiveReportRequest;
use App\Modules\AbusiveReport\Http\Resources\AbusiveReportResource;
use App\Modules\AbusiveReport\Services\AbusiveReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbusiveReportController extends BaseController
{
    public function __construct(
        private readonly AbusiveReportService $queryService,
        private readonly CreateReportAction $createAction,
        private readonly DeleteReportAction $deleteAction,
        private readonly AcceptReportAction $acceptAction,
        private readonly RejectReportAction $rejectAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AbusiveReport::class);

        $perPage = (int) $request->input('limit', 15);
        $perPage = max(1, min($perPage, 100));

        $paginator = $this->queryService->getReports($perPage);

        return $this->sendPaginated(
            $paginator,
            AbusiveReportResource::collection($paginator->getCollection()),
            'Daftar laporan berhasil diambil.'
        );
    }

    public function store(CreateAbusiveReportRequest $request): JsonResponse
    {

        $data = AbusiveReportData::fromRequest(
            $request->validated(),
            (int) $request->user()->id
        );

        $report = $this->createAction->execute($data, $request->user());

        return $this->sendSuccess(
            new AbusiveReportResource($report),
            'Laporan berhasil dibuat.',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $report = $this->queryService->findOrFail($id);
        $this->authorize('view', $report);

        return $this->sendSuccess(
            new AbusiveReportResource($report),
            'Data laporan berhasil diambil.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $report = $this->queryService->findOrFail($id);
        $this->authorize('delete', $report);

        $this->deleteAction->execute($report);

        return $this->sendSuccess(null, 'Laporan berhasil dihapus.');
    }

    public function accept(AcceptOrRejectAbusiveReportRequest $request): JsonResponse
    {
        $this->authorize('accept', AbusiveReport::class);

        $this->acceptAction->execute(
            $request->validated('model_type'),
            (int) $request->validated('model_id')
        );

        return $this->sendSuccess(null, 'Laporan berhasil diterima.');
    }

    public function reject(AcceptOrRejectAbusiveReportRequest $request): JsonResponse
    {
        $this->authorize('reject', AbusiveReport::class);

        $this->rejectAction->execute(
            $request->validated('model_type'),
            (int) $request->validated('model_id')
        );

        return $this->sendSuccess(null, 'Laporan berhasil ditolak.');
    }

    public function myReports(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->input('limit', 15);
        $perPage = max(1, min($perPage, 100));

        $paginator = $this->queryService->getUserReports($user->id, $perPage);

        return $this->sendPaginated(
            $paginator,
            AbusiveReportResource::collection($paginator->getCollection()),
            'Daftar laporan Anda berhasil diambil.'
        );
    }
}
