<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\DTO\MessageListRequest;
use App\Entity\Message;
use App\Message\SendMessage;
use App\Repository\MessageRepositoryInterface;
use App\Service\MessageService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageServiceTest extends TestCase
{
    public function testGetFormattedMessagesFromRequest(): void
    {

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageRepository = $this->createMock(MessageRepositoryInterface::class);
        $messageService = new MessageService($messageBus, $messageRepository);
        $messageListRequest = $this->createMock(MessageListRequest::class);


        $message1 = $this->createMock(Message::class);
        $message1->method('getUuid')->willReturn('uuid-1');
        $message1->method('getText')->willReturn('Hello');
        $message1->method('getStatus')->willReturn('sent');

        $message2 = $this->createMock(Message::class);
        $message2->method('getUuid')->willReturn('uuid-2');
        $message2->method('getText')->willReturn('World');
        $message2->method('getStatus')->willReturn('received');

        $messageRepository->expects($this->once())
            ->method('by')
            ->with($messageListRequest)
            ->willReturn([$message1, $message2]);


        $result = $messageService->getFormattedMessagesFromRequest($messageListRequest);

        $this->assertCount(2, $result);
        $this->assertEquals([
            'uuid' => 'uuid-1',
            'text' => 'Hello',
            'status' => 'sent',
        ], $result[0]);

        $this->assertEquals([
            'uuid' => 'uuid-2',
            'text' => 'World',
            'status' => 'received',
        ], $result[1]);
    }

    public function testFormatMessages(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageRepository = $this->createMock(MessageRepositoryInterface::class);
        $messageService = new MessageService($messageBus, $messageRepository);

        $message1 = $this->createMock(Message::class);
        $message1->method('getUuid')->willReturn('uuid-1');
        $message1->method('getText')->willReturn('Test message 1');
        $message1->method('getStatus')->willReturn('sent');

        $message2 = $this->createMock(Message::class);
        $message2->method('getUuid')->willReturn('uuid-2');
        $message2->method('getText')->willReturn('Test message 2');
        $message2->method('getStatus')->willReturn('received');

        $result = $messageService->formatMessages([$message1, $message2]);

        $this->assertCount(2, $result);
        $this->assertEquals([
            'uuid' => 'uuid-1',
            'text' => 'Test message 1',
            'status' => 'sent',
        ], $result[0]);

        $this->assertEquals([
            'uuid' => 'uuid-2',
            'text' => 'Test message 2',
            'status' => 'received',
        ], $result[1]);
    }
}
