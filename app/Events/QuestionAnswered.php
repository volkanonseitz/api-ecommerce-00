<?php

namespace App\Events;

use App\Models\Question;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionAnswered
{
    use Dispatchable, SerializesModels;

    public Question $question;

    public function __construct(Question $question)
    {
        $this->question = $question;
    }
}