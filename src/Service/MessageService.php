<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\MessageListRequest;
use App\Entity\Message;
use App\Message\SendMessage;
use App\Repository\MessageRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageService
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly MessageRepositoryInterface $messageRepository
    ) {}

    /**
     * Get formatted messages based on the provided request.
     *
     * @param MessageListRequest $messageListRequest The request containing filter criteria.
     *
     * @return array<int, array<string, mixed>> The list of formatted messages, where each message is an associative array.
     */
    public function getFormattedMessagesFromRequest(MessageListRequest $messageListRequest): array
    {
        $messages = $this->messageRepository->by($messageListRequest);

        return $this->formatMessages($messages);
    }

    /**
     * Dispatch a message with the given text.
     *
     * @param string $text The text to be sent.
     *
     * @return void
     */
    public function dispatchMessage(string $text): void
    {
        $this->bus->dispatch(new SendMessage($text));
    }

    /**
     * Format a list of messages.
     *
     * @param Message[] $messages The list of messages to format.
     *
     * @return array<int, array<string, mixed>> The formatted messages, where each message is an associative array.
     */
    public function formatMessages(array $messages): array
    {
        return array_map(fn(Message $message) => $this->formatMessage($message), $messages);
    }

    /**
     * Format a single message into an array.
     *
     * @param Message $message The message to format.
     *
     * @return array<string, mixed> The formatted message.
     */
    private function formatMessage(Message $message): array
    {
        return [
            'uuid' => $message->getUuid(),
            'text' => $message->getText(),
            'status' => $message->getStatus(),
        ];
    }
}
