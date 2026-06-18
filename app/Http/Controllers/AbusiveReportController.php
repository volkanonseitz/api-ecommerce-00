<?php

namespace App\Http\Controllers;

use App\DTO\AbusiveReportData;
use App\Http\Requests\AbusiveReportAcceptOrRejectRequest;
use App\Http\Requests\AbusiveReportCreateRequest;
use App\Http\Resources\AbusiveReportResource;
use App\Services\AbusiveReportService;
use Illuminate\Http\Request;

class AbusiveReportController extends BaseController
{
    public function __construct(
        private readonly AbusiveReportService $service
    ) {}

    public function index(Request $request)
    {
        $limit = max(
            1,
            min((int) $request->input('limit', 15), 100)
        );

        $reports = $this->service->getReports($limit);

        return $this->sendPaginated(
            $reports,
            AbusiveReportResource::collection($reports->getCollection()),
            'Daftar laporan berhasil diambil.'
        );
    }

    public function store(AbusiveReportCreateRequest $request)
    {
        $data = AbusiveReportData::fromRequest(
            $request->validated(),
            $request->user()->id
        );

        $report = $this->service->createReport($data);

        return $this->sendSuccess(
            new AbusiveReportResource($report),
            'Laporan berhasil dibuat.',
            201
        );
    }

    public function show(int $id)
    {
        $report = $this->service->findOrFail($id);

        return $this->sendSuccess(
            new AbusiveReportResource($report),
            'Data laporan berhasil diambil.'
        );
    }

    public function destroy(int $id)
    {
        $this->service->deleteReport($id);

        return $this->sendSuccess(
            null,
            'Laporan berhasil dihapus.'
        );
    }

    public function accept(AbusiveReportAcceptOrRejectRequest $request)
    {
        $this->service->acceptReport(
            $request->model_type,
            $request->model_id
        );

        return $this->sendSuccess(
            null,
            'Laporan berhasil diterima.'
        );
    }

    public function reject(AbusiveReportAcceptOrRejectRequest $request)
    {
        $this->service->rejectReport(
            $request->model_type,
            $request->model_id
        );

        return $this->sendSuccess(
            null,
            'Laporan berhasil ditolak.'
        );
    }

    public function myReports(Request $request)
    {
        $limit = max(
            1,
            min((int) $request->input('limit', 15), 100)
        );

        $reports = $this->service->getUserReports(
            $request->user()->id,
            $limit
        );

        return $this->sendPaginated(
            $reports,
            AbusiveReportResource::collection($reports->getCollection()),
            'Daftar laporan berhasil diambil.'
        );
    }
}
