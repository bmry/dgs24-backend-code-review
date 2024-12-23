<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Message\SendMessage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MessageControllerTest extends WebTestCase
{
    use InteractsWithMessenger;

    public function test_list(): void
    {
        $client = static::createClient();

        $client->request('GET', '/messages', [
            'query' => ['status' => 'sent']
        ]);

        $this->assertResponseIsSuccessful();

        /** @var string $responseContent */
        $responseContent = $client->getResponse()->getContent();

        $response = json_decode($responseContent, true);

        $this->assertIsArray($response, 'Response is not a valid array.');

        $this->assertArrayHasKey('messages', $response);
        $this->assertIsArray($response['messages']);

        foreach ($response['messages'] as $message) {
            $this->assertArrayHasKey('uuid', $message);
            $this->assertArrayHasKey('text', $message);
            $this->assertArrayHasKey('status', $message);
        }
    }


    function test_that_it_sends_a_message(): void
    {
        $client = static::createClient();
        $client->request('POST', '/messages/send', [
            'text' => 'Hello World',
        ]);

        $this->assertResponseIsSuccessful();
        // This is using https://packagist.org/packages/zenstruck/messenger-test
        $this->transport('sync')
            ->queue()
            ->assertContains(SendMessage::class, 1);
    }
}
