<?php

declare(strict_types=1);

namespace App\Modules\Faqs\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Faqs;
use App\Models\Shop;
use App\Modules\Faqs\DTO\FaqsData;
use App\Modules\Faqs\Http\Requests\FaqsCreateRequest;
use App\Modules\Faqs\Http\Requests\FaqsUpdateRequest;
use App\Modules\Faqs\Http\Resources\FaqResource;
use App\Modules\Faqs\Services\FaqsService;
use Illuminate\Http\Request;

class FaqsController extends BaseController
{
    public function __construct(private FaqsService $faqsService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Faqs::class);
        $limit = (int) ($request->limit ?? 10);
        $faqs = $this->faqsService->getFaqsQuery($request, $request->user())->paginate($limit);

        return FaqResource::collection($faqs);
    }

    public function store(FaqsCreateRequest $request)
    {
        $this->authorize('create', Faqs::class);

        $user = $request->user();
        $shopId = $request->shop_id;

        // Tentukan faq_type dan issued_by
        if ($shopId) {
            $shop = Shop::find($shopId);
            if (! $shop) {
                return $this->sendError('Shop not found', 404);
            }
            $faqType = 'shop';
            $issuedBy = $shop->name;
        } else {
            $faqType = 'global';
            $issuedBy = 'Super Admin';
        }

        $data = FaqsData::fromRequest($request->validated(), $user->id);
        // Override faq_type dan issued_by
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

    public function show(int $id)
    {
        $faq = $this->faqsService->findOrFail($id);
        $this->authorize('view', $faq);

        return new FaqResource($faq);
    }

    public function update(FaqsUpdateRequest $request, int $id)
    {
        $faq = Faqs::findOrFail($id);
        $this->authorize('update', $faq);

        $data = FaqsData::fromRequest($request->validated(), $faq->user_id);
        $updated = $this->faqsService->update($faq, $data);

        return new FaqResource($updated);
    }

    public function destroy(Request $request, int $id)
    {
        $faq = Faqs::findOrFail($id);
        $this->authorize('delete', $faq);
        $this->faqsService->delete($faq);

        return $this->sendSuccess(null, 'FAQ deleted successfully');
    }
}
