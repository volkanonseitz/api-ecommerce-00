<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use App\Http\Requests\SettingsRequest;
use App\DTO\SettingsData;
use App\Events\Maintenance;
use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private SettingsService $settingsService) {}

    /**
     * GET /settings
     */
    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $data = $this->settingsService->getSettingsWithMaintenance($language);
        return response()->json($data);
    }

    /**
     * POST /settings (store)
     */
    public function store(SettingsRequest $request)
    {
        $data = SettingsData::fromRequest($request->validated());
        $settings = $this->settingsService->storeOrUpdate($data, true);
        event(new Maintenance($data->language));
        return response()->json($settings);
    }

    /**
     * GET /settings/{id} (show first)
     */
    public function show($id)
    {
        $settings = $this->settingsService->getFirst();
        if (!$settings) {
            abort(404, config('notice.NOT_FOUND'));
        }
        return response()->json($settings);
    }

    /**
     * PUT /settings/{id} (update)
     */
    public function update(SettingsRequest $request, $id)
    {
        $data = SettingsData::fromRequest($request->validated());
        $settings = $this->settingsService->storeOrUpdate($data, false);
        return response()->json($settings);
    }

    /**
     * DELETE /settings/{id} (not allowed)
     */
    public function destroy($id)
    {
        throw new \Exception(config('notice.ACTION_NOT_VALID'));
    }
}