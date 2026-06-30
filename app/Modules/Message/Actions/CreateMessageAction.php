<?php

declare(strict_types=1);

namespace App\Modules\Message\Actions;

use App\Models\Message;
use App\Modules\Message\DTO\MessageData;

class CreateMessageAction
{
    public function execute(MessageData $data): Message
    {
        return Message::create($data->toArray());
    }
}
