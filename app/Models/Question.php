<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use SoftDeletes;

    protected $table = 'questions';
    protected $guarded = [];
    protected $appends = [
        'positive_feedbacks_count',
        'negative_feedbacks_count',
        'my_feedback',
        'abusive_reports_count',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function feedbacks()
    {
        return $this->morphMany(Feedback::class, 'model');
    }

    public function abusive_reports()
    {
        return $this->morphOne(AbusiveReport::class, 'model');
    }

    public function getPositiveFeedbacksCountAttribute()
    {
        return $this->feedbacks()->where('positive', 1)->count();
    }

    public function getNegativeFeedbacksCountAttribute()
    {
        return $this->feedbacks()->where('negative', 1)->count();
    }

    public function getMyFeedbackAttribute()
    {
        if (auth()->check()) {
            return $this->feedbacks()->where('user_id', auth()->id())->first();
        }
        return null;
    }

    public function getAbusiveReportsCountAttribute()
    {
        return $this->abusive_reports()->count();
    }
}