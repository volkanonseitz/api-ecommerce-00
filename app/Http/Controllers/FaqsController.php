<?php

namespace App\Http\Controllers;

use App\Services\FaqsService;
use App\Http\Requests\FaqsCreateRequest;
use App\Http\Requests\FaqsUpdateRequest;
use App\Http\Resources\FaqResource;
use App\DTO\FaqsData;
use App\Models\Faqs;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class FaqsController extends Controller
{
    public function __construct(private FaqsService $faqsService) {}

    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;
        $faqs = $this->faqsService->getFaqsQuery($request, $request->user())->paginate($limit);
        $data = FaqResource::collection($faqs)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    public function store(FaqsCreateRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        // Untuk store, hanya super admin, store owner, staff yang punya permission di shop tertentu bisa
        if (!$user->hasPermissionTo(\App\Enums\Permission::SUPER_ADMIN->value) && !$this->faqsService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        // Tentukan faq_type dan issued_by
        if ($shopId) {
            $shop = Shop::find($shopId);
            $faqType = 'shop';
            $issuedBy = $shop->name;
        } else {
            $faqType = 'global';
            $issuedBy = 'Super Admin';
        }
        $data = FaqsData::fromRequest($request->validated(), $user->id);
        // override
        $data = new FaqsData(
            faq_title: $data->faq_title,
            faq_description: $data->faq_description,
            language: $data->language,
            slug: $data->slug,
            user_id: $data->user_id,
            shop_id: $data->shop_id,
            faq_type: $faqType,
            issued_by: $issuedBy,
        );
        $faq = $this->faqsService->store($data);
        return new FaqResource($faq);
    }

    public function show($id)
    {
        $faq = $this->faqsService->findOrFail($id);
        return new FaqResource($faq);
    }

    public function update(FaqsUpdateRequest $request, $id)
    {
        $faq = Faqs::findOrFail($id);
        $user = $request->user();
        // Cek permission: hanya super admin atau pemilik shop atau staff dengan shop yang sama
        if (!$user->hasPermissionTo(\App\Enums\Permission::SUPER_ADMIN->value) && !$this->faqsService->hasPermission($user, $faq->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = FaqsData::fromRequest($request->validated(), $faq->user_id);
        $updated = $this->faqsService->update($faq, $data);
        return new FaqResource($updated);
    }

    public function destroy(Request $request, $id)
    {
        $faq = Faqs::findOrFail($id);
        $user = $request->user();
        if (!$user->hasPermissionTo(\App\Enums\Permission::SUPER_ADMIN->value) && !$this->faqsService->hasPermission($user, $faq->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $this->faqsService->delete($faq);
        return response()->json(['message' => 'FAQ deleted successfully']);
    }
}