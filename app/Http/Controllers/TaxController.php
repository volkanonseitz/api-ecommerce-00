<?php

namespace App\Http\Controllers;

use App\Services\TaxService;
use App\Http\Requests\TaxCreateRequest;
use App\Http\Requests\TaxUpdateRequest;

class TaxController extends Controller
{
    public function __construct(private TaxService $taxService) {}

    public function index()
    {
        return response()->json($this->taxService->getAll());
    }

    public function store(TaxCreateRequest $request)
    {
        $tax = $this->taxService->create($request->validated());
        return response()->json($tax);
    }

    public function show($id)
    {
        $tax = $this->taxService->find($id);
        return response()->json($tax);
    }

    public function update(TaxUpdateRequest $request, $id)
    {
        $tax = $this->taxService->update($id, $request->validated());
        return response()->json($tax);
    }

    public function destroy($id)
    {
        $this->taxService->delete($id);
        return response()->json(['message' => 'Tax deleted successfully']);
    }
}