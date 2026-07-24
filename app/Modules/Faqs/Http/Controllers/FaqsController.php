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
use App\Modules\Faqs\Services\FaqsQueryService;
use App\Modules\Faqs\Services\FaqsWriteService;
use Illuminate\Http\Request;

class FaqsController extends BaseController
{
    public function __construct(
        private readonly FaqsQueryService $faqsQueryService,
        private readonly FaqsWriteService $faqsWriteService
    ) {}

    public function index(Request $request)
    {
        $limit = (int) ($request->limit ?? 10);
        $faqs = $this->faqsQueryService->getFaqsQuery($request, $request->user())->paginate($limit);

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

        $faq = $this->faqsWriteService->store($data);

        return new FaqResource($faq);
    }

    public function show(int $id)
    {
        $faq = $this->faqsQueryService->findOrFail($id);

        return new FaqResource($faq);
    }

    public function update(FaqsUpdateRequest $request, int $id)
    {
        $faq = Faqs::findOrFail($id);
        $this->authorize('update', $faq);

        $data = FaqsData::fromRequest($request->validated(), $faq->user_id);
        $updated = $this->faqsWriteService->update($faq, $data);

        return new FaqResource($updated);
    }

    public function destroy(Request $request, int $id)
    {
        $faq = Faqs::findOrFail($id);
        $this->authorize('delete', $faq);
        $this->faqsWriteService->delete($faq);

        return $this->sendSuccess(null, 'FAQ deleted successfully');
    }
}
