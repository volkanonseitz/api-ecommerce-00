<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use App\Http\Requests\SettingsRequest;
use App\DTO\SettingsData;
use App\Events\Maintenance;
use App\Models\Settings;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;

class SettingsController extends BaseController
{
    public function __construct(private SettingsService $settingsService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = "settings_{$language}";
        $data = Cache::rememberForever($cacheKey, function () use ($language) {
            return $this->settingsService->getSettingsWithMaintenance($language);
        });
        return $this->sendSuccess($data, 'Settings retrieved');
    }

    public function store(SettingsRequest $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = SettingsData::fromRequest($request->validated());
        $settings = $this->settingsService->storeOrUpdate($data, true);
        event(new Maintenance($data->language));
        Cache::forget("settings_{$data->language}");
        return $this->sendSuccess($settings, 'Settings created', 201);
    }

    public function show($id)
    {
        // Biasanya hanya mengambil settings pertama, tanpa otorisasi karena public
        $settings = $this->settingsService->getFirst();
        if (!$settings) {
            return $this->sendError('Settings not found', 404);
        }
        return $this->sendSuccess($settings, 'Settings detail');
    }

    public function update(SettingsRequest $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = SettingsData::fromRequest($request->validated());
        $settings = $this->settingsService->storeOrUpdate($data, false);
        Cache::forget("settings_{$data->language}");
        return $this->sendSuccess($settings, 'Settings updated');
    }

    public function destroy($id)
    {
        throw new \Exception(config('notice.ACTION_NOT_VALID'));
    }
}