<?php

namespace App\Actions;

use App\DTO\MessageData;
use App\Models\Message;

class CreateMessageAction
{
    public function execute(MessageData $data): Message
    {
        return Message::create($data->toArray());
    }
}
