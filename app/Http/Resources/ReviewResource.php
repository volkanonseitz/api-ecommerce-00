<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'order_id' => $this->resource->order_id,
            'product_id' => $this->resource->product_id,
            'variation_option_id' => $this->resource->variation_option_id,
            'user_id' => $this->resource->user_id,
            'shop_id' => $this->resource->shop_id,
            'comment' => $this->resource->comment,
            'rating' => $this->resource->rating,
            'photos' => $this->resource->photos,
            'positive_feedbacks_count' => $this->resource->positive_feedbacks_count,
            'negative_feedbacks_count' => $this->resource->negative_feedbacks_count,
            'my_feedback' => $this->resource->my_feedback,
            'abusive_reports_count' => $this->resource->abusive_reports_count,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'deleted_at' => $this->resource->deleted_at,
        ];
    }
}
