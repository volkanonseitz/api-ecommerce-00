<?php

namespace App\Http\Controllers;

use App\Services\TermsService;
use App\Http\Requests\TermsAndConditionsCreateRequest;
use App\Http\Requests\TermsAndConditionsUpdateRequest;
use App\Http\Resources\TermsConditionResource;
use App\DTO\TermsData;
use App\Models\TermsAndConditions;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;

class TermsAndConditionsController extends BaseController
{
    public function __construct(private TermsService $termsService) {}

    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;
        $terms = $this->termsService->getTermsQuery($request, $request->user())->paginate($limit);
        return $this->sendPaginated($terms, 'Terms retrieved');
    }

    public function store(TermsAndConditionsCreateRequest $request)
    {
        $user = $request->user();
        // Hanya super admin atau store owner yang boleh membuat terms
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value) && !$user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = TermsData::fromRequest($request->validated(), $user->id);
        $term = $this->termsService->store($data);
        Cache::forget("terms_{$data->language}_*");
        return $this->sendSuccess(new TermsConditionResource($term), 'Terms created', 201);
    }

    public function show(Request $request, $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $term = TermsAndConditions::where('slug', $slug)->where('language', $language)->firstOrFail();
        return $this->sendSuccess(new TermsConditionResource($term), 'Terms detail');
    }

    public function update(TermsAndConditionsUpdateRequest $request, $id)
    {
        $user = $request->user();
        $term = TermsAndConditions::findOrFail($id);
        // Hanya super admin atau pemilik toko yang bersangkutan (jika shop_id terkait)
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value) && !$this->termsService->hasPermission($user, $term->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $updated = $this->termsService->update($term, $request->only(['title', 'description', 'slug', 'language']));
        Cache::forget("terms_{$term->language}_*");
        return $this->sendSuccess(new TermsConditionResource($updated), 'Terms updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $term = TermsAndConditions::findOrFail($id);
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value) && !$user->hasPermissionTo(Permission::STORE_OWNER->value) && !$user->hasPermissionTo(Permission::STAFF->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $this->termsService->delete($term);
        Cache::forget("terms_{$term->language}_*");
        return $this->sendSuccess(null, 'Terms deleted');
    }

    public function approveTerm(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $term = TermsAndConditions::findOrFail($request->id);
        $this->termsService->approve($term);
        Cache::forget("terms_{$term->language}_*");
        return $this->sendSuccess(new TermsConditionResource($term), 'Term approved');
    }

    public function disApproveTerm(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $term = TermsAndConditions::findOrFail($request->id);
        $this->termsService->disapprove($term);
        Cache::forget("terms_{$term->language}_*");
        return $this->sendSuccess(new TermsConditionResource($term), 'Term disapproved');
    }
}