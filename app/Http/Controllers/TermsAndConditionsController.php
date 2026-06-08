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
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TermsAndConditionsController extends Controller
{
    public function __construct(private TermsService $termsService) {}

    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;
        $terms = $this->termsService->getTermsQuery($request, $request->user())->paginate($limit);
        $data = TermsConditionResource::collection($terms)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    public function store(TermsAndConditionsCreateRequest $request)
    {
        $data = TermsData::fromRequest($request->validated(), $request->user()->id);
        $term = $this->termsService->store($data);
        return new TermsConditionResource($term);
    }

    public function show(Request $request, $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $term = TermsAndConditions::where('slug', $slug)->where('language', $language)->firstOrFail();
        return new TermsConditionResource($term);
    }

    public function update(TermsAndConditionsUpdateRequest $request, $id)
    {
        $term = TermsAndConditions::findOrFail($id);
        $updated = $this->termsService->update($term, $request->only(['title', 'description', 'slug', 'language']));
        return new TermsConditionResource($updated);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $term = TermsAndConditions::findOrFail($id);

        if (!$user || (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value) && !$user->hasPermissionTo(Permission::STORE_OWNER->value) && !$user->hasPermissionTo(Permission::STAFF->value))) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $this->termsService->delete($term);
        return response()->json(['message' => 'Terms and conditions deleted successfully']);
    }

    public function approveTerm(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $term = TermsAndConditions::findOrFail($request->id);
        $this->termsService->approve($term);
        return new TermsConditionResource($term);
    }

    public function disApproveTerm(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $term = TermsAndConditions::findOrFail($request->id);
        $this->termsService->disapprove($term);
        return new TermsConditionResource($term);
    }
}