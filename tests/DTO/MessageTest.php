<?php

namespace fholbrook\Openrouter\Tests\DTO;

use PHPUnit\Framework\TestCase;
use fholbrook\Openrouter\DTO\Message;
use fholbrook\Openrouter\Contracts\StampInterface;
use fholbrook\Openrouter\Stamps\ModelStamp;

class MessageTest extends TestCase
{
    public function testConstruction()
    {
        $message = new Message(
            'test content',
            'user',
            null,
            'John',
            []
        );

        $this->assertEquals('test content', $message->content);
        $this->assertEquals('user', $message->role);
        $this->assertNull($message->toolCalls);
        $this->assertEquals('John', $message->name);
        $this->assertEmpty($message->stamps);
    }

    public function testGetStampByFQDN()
    {
        $stamp = $this->createMock(StampInterface::class);
        $message = new Message();
        $message->addStamp($stamp);

        $result = $message->getStampByFQDN(get_class($stamp));
        $this->assertSame($stamp, $result);

        $nonExistentResult = $message->getStampByFQDN('NonExistentClass');
        $this->assertNull($nonExistentResult);
    }

    public function testAddStamp()
    {
        $message = new Message();
        $stamp = $this->createMock(StampInterface::class);
        
        $message->addStamp($stamp);
        $this->assertCount(1, $message->stamps);
        $this->assertSame($stamp, $message->stamps[0]);

        // Test adding null stamp
        $message->addStamp(null);
        $this->assertCount(1, $message->stamps);
    }


    public function testFromArray()
    {
        $data = [
            'content' => 'test content',
            'role' => 'user',
            'name' => 'John'
        ];

        $message = Message::fromArray($data);

        $this->assertEquals('test content', $message->content);
        $this->assertEquals('user', $message->role);
        $this->assertEquals('John', $message->name);
    }

    public function testToArrayFiltersNullsAndOmitsStampsByDefault(): void
    {
        $message = new Message(content: 'hello', role: 'user');
        $message->addStamp(new ModelStamp('m'));

        $array = $message->toArray();

        $this->assertSame(['content' => 'hello', 'role' => 'user'], $array);
        $this->assertArrayNotHasKey('stamps', $array);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayNotHasKey('toolCalls', $array);
    }

    public function testToArrayIncludesStampsWithFqdnWhenRequested(): void
    {
        $message = new Message(content: 'hi', role: 'assistant');
        $message->addStamp(new ModelStamp('openai/gpt-4'));

        $array = $message->toArray(includeStamps: true);

        $this->assertCount(1, $array['stamps']);
        $this->assertSame('openai/gpt-4', $array['stamps'][0]['model']);
        $this->assertSame(ModelStamp::class, $array['stamps'][0]['fqdn']);
    }
}
