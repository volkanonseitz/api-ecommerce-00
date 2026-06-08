<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'variation_option_id' => $this->variation_option_id,
            'user_id' => $this->user_id,
            'shop_id' => $this->shop_id,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'photos' => $this->photos,
            'positive_feedbacks_count' => $this->positive_feedbacks_count,
            'negative_feedbacks_count' => $this->negative_feedbacks_count,
            'my_feedback' => $this->my_feedback,
            'abusive_reports_count' => $this->abusive_reports_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}