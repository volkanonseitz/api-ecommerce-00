<?php

namespace App\Http\Controllers;

use App\Services\LanguageService;
use App\Http\Requests\LanguageRequest;
use App\DTO\LanguageData;
use App\Models\Language;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;

class LanguageController extends BaseController
{
    public function __construct(private LanguageService $languageService) {}

    public function index()
    {
        $languages = Cache::rememberForever('languages_all', function () {
            return $this->languageService->getAll();
        });
        return $this->sendSuccess($languages, 'Languages retrieved');
    }

    public function store(LanguageRequest $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = LanguageData::fromRequest($request->validated());
        $language = $this->languageService->create($data);
        Cache::forget('languages_all');
        return $this->sendSuccess($language, 'Language created', 201);
    }

    public function show($params)
    {
        $language = $this->languageService->find($params);
        return $this->sendSuccess($language, 'Language detail');
    }

    public function update(LanguageRequest $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $language = Language::findOrFail($id);
        $data = LanguageData::fromRequest($request->validated());
        $updated = $this->languageService->update($language, $data);
        Cache::forget('languages_all');
        return $this->sendSuccess($updated, 'Language updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $language = Language::findOrFail($id);
        $this->languageService->delete($language);
        Cache::forget('languages_all');
        return $this->sendSuccess(null, 'Language deleted');
    }
}