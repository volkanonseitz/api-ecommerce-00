<?php

namespace App\Http\Controllers;

use App\Services\LanguageService;
use App\Http\Requests\LanguageRequest;
use App\DTO\LanguageData;
use App\Models\Language;

class LanguageController extends Controller
{
    public function __construct(private LanguageService $languageService) {}

    public function index()
    {
        $languages = $this->languageService->getAll();
        return response()->json($languages);
    }

    public function store(LanguageRequest $request)
    {
        $data = LanguageData::fromRequest($request->validated());
        $language = $this->languageService->create($data);
        return response()->json($language);
    }

    public function show($params)
    {
        $language = $this->languageService->find($params);
        return response()->json($language);
    }

    public function update(LanguageRequest $request, $id)
    {
        $language = Language::findOrFail($id);
        $data = LanguageData::fromRequest($request->validated());
        $updated = $this->languageService->update($language, $data);
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $language = Language::findOrFail($id);
        $this->languageService->delete($language);
        return response()->json(['message' => 'Language deleted successfully']);
    }
}