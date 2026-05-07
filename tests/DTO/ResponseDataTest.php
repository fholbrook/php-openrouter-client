<?php

namespace fholbrook\Openrouter\Tests\DTO;

use fholbrook\Openrouter\DTO\ResponseData;
use fholbrook\Openrouter\DTO\NonChatChoice;
use fholbrook\Openrouter\DTO\Message;
use fholbrook\Openrouter\DTO\Usage;
use PHPUnit\Framework\TestCase;

class ResponseDataTest extends TestCase
{
    public function testConstructor()
    {
        $response = new ResponseData(
            'test-id',
            'test-model',
            'chat.completion',
            1715621307,
            'test-provider',
            null,
            null,
            null
        );

        $this->assertEquals('test-id', $response->id);
        $this->assertEquals('test-model', $response->model);
        $this->assertEquals('chat.completion', $response->object);
        $this->assertEquals(1715621307, $response->created);
        $this->assertEquals('test-provider', $response->provider);
        $this->assertNull($response->choices);
        $this->assertNull($response->usage);
        $this->assertNull($response->citations);
    }

    public function testFromArrayWithNonChatChoice()
    {
        $data = [
            'id' => 'test-id',
            'model' => 'test-model',
            'object' => 'chat.completion',
            'created' => 1715621307,
            'provider' => 'test-provider',
            'choices' => [
                [
                    'text' => 'test text',
                    'finishReason' => 'stop'
                ]
            ],
            'usage' => [
                'promptTokens' => 10,
                'completionTokens' => 20,
                'totalTokens' => 30
            ],
            'citations' => ['citation1', 'citation2']
        ];

        $response = ResponseData::fromArray($data);

        $this->assertInstanceOf(ResponseData::class, $response);
        $this->assertEquals('test-id', $response->id);
        $this->assertInstanceOf(NonChatChoice::class, $response->choices[0]);
        $this->assertEquals('test text', $response->choices[0]->text);
        $this->assertInstanceOf(Usage::class, $response->usage);
        $this->assertEquals(['citation1', 'citation2'], $response->citations);
    }

    public function testFromArrayWithMessage()
    {
        $data = [
            'id' => 'test-id',
            'model' => 'test-model',
            'object' => 'chat.completion',
            'created' => 1715621307,
            'provider' => 'test-provider',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'test content'
                    ]
                ]
            ],
            'usage' => null,
            'citations' => null
        ];

        $response = ResponseData::fromArray($data);

        $this->assertInstanceOf(ResponseData::class, $response);
        $this->assertEquals('test-id', $response->id);
        $this->assertInstanceOf(Message::class, $response->choices[0]);
        $this->assertEquals('assistant', $response->choices[0]->role);
        $this->assertEquals('test content', $response->choices[0]->content);
        $this->assertNull($response->usage);
        $this->assertNull($response->citations);
    }
}
