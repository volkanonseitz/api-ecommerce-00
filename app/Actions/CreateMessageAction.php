<?php

namespace App\Actions;

use App\Models\Message;
use App\DTO\MessageData;

class CreateMessageAction
{
    public function execute(MessageData $data): Message
    {
        return Message::create($data->toArray());
    }
}