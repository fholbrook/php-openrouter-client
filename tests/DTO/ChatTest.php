<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\DTO;

use fholbrook\Openrouter\DTO\Chat;
use fholbrook\Openrouter\DTO\Message;
use PHPUnit\Framework\TestCase;

class ChatTest extends TestCase
{
    public function testToArray(): void
    {
        $message = new Message('Hello', 'user');
        $chat = new Chat('123', [$message]);

        $expected = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello'
                ]
            ]
        ];

        $this->assertEquals($expected, $chat->toArray());
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => '123',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello',
                    'id' => null,
                    'name' => null
                ]
            ]
        ];

        $chat = Chat::fromArray($data);

        $this->assertEquals('123', $chat->id);
        $this->assertCount(1, $chat->messages);
        $this->assertEquals('user', $chat->messages[0]->role);
        $this->assertEquals('Hello', $chat->messages[0]->content);
    }

    public function testToArrayWithMetadataIncludesId(): void
    {
        $chat = new Chat('chat-id-123', [new Message('Hello', 'user')]);

        $array = $chat->toArray(includeMetadata: true);

        $this->assertSame('chat-id-123', $array['id']);
        $this->assertCount(1, $array['messages']);
    }

    public function testToArrayWithEmptyMessagesAndMetadataKeepsId(): void
    {
        $chat = new Chat('only-id');

        $array = $chat->toArray(includeMetadata: true);

        $this->assertSame('only-id', $array['id']);
        $this->assertArrayNotHasKey('messages', $array, 'empty messages array is filtered');
    }
}
