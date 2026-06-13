<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AbusiveReportService;
use App\Http\Requests\AbusiveReportCreateRequest;
use App\Http\Requests\AbusiveReportAcceptOrRejectRequest;
use App\Http\Resources\AbusiveReportResource;
use App\DTO\AbusiveReportData;

class AbusiveReportController extends Controller
{
    public function __construct(private AbusiveReportService $service) {}

    /**
     * GET /abusive-reports
     */
    public function index()
    {
        $reports = $this->service->getReports();
        return AbusiveReportResource::collection($reports);
    }

    /**
     * POST /abusive-reports
     */
    public function store(AbusiveReportCreateRequest $request)
    {
        $data = AbusiveReportData::fromRequest($request->validated(), $request->user()->id);
        $report = $this->service->createReport($data);
        return new AbusiveReportResource($report);
    }

    /**
     * GET /abusive-reports/{id}
     */
    public function show($id)
    {
        $report = $this->service->findOrFail($id);
        return new AbusiveReportResource($report);
    }

    /**
     * DELETE /abusive-reports/{id}
     */
    public function destroy($id)
    {
        $this->service->deleteReport($id);
        return response()->json(['message' => 'Report deleted successfully']);
    }

    /**
     * POST /abusive-reports/accept
     */
    public function accept(AbusiveReportAcceptOrRejectRequest $request)
    {
        $this->service->acceptReport($request->model_type, $request->model_id);
        return response()->json(['message' => 'Report accepted and content deleted']);
    }

    /**
     * POST /abusive-reports/reject
     */
    public function reject(AbusiveReportAcceptOrRejectRequest $request)
    {
        $this->service->rejectReport($request->model_type, $request->model_id);
        return response()->json(['message' => 'Report rejected']);
    }

    /**
     * GET /my-reports
     */
    public function myReports(Request $request)
    {
        $limit = $request->limit ?? 15;
        $reports = $this->service->getUserReports($request->user()->id, $limit);
        return AbusiveReportResource::collection($reports);
    }
}