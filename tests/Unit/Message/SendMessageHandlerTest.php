<?php

namespace App\Tests\Unit\Message;

use App\Entity\Message;
use App\Message\SendMessage;
use App\Message\SendMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SendMessageHandlerTest extends TestCase
{
    public function testInvokePersistsMessage(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Message $message) {
                $this->assertNotEmpty($message->getUuid());
                $this->assertSame('Test message', $message->getText());
                $this->assertSame('sent', $message->getStatus());
                $this->assertInstanceOf(\DateTime::class, $message->getCreatedAt());

                return true;
            }));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $handler = new SendMessageHandler($entityManager);


        $sendMessage = new SendMessage('Test message');
        $sendMessage->text = 'Test message';

        $handler($sendMessage);
    }
}
