<?php

namespace App\Repository;

use App\DTO\MessageListRequest;
use App\Entity\Message;

interface MessageRepositoryInterface
{
    /**
     * Get a list of messages based on the given request.
     *
     * @param MessageListRequest $messageListRequest The request containing filter criteria.
     *
     * @return Message[] The list of messages.
     */
    public function by(MessageListRequest $messageListRequest): array;
}
