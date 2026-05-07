<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\DTO;

use fholbrook\Openrouter\DTO\Property;
use fholbrook\Openrouter\DTO\Schema;
use fholbrook\Openrouter\DTO\ToolCall;
use fholbrook\Openrouter\DTO\FunctionData;
use PHPUnit\Framework\TestCase;

class ToolCallTest extends TestCase
{
    public function testToArray(): void
    {
        $functionData = new FunctionData('test_name', null, 'desc', new Schema('object', [new Property('zipcode', 'string', 'The zipcode to fetch weather for')]));
        
        $toolCall = new ToolCall(
            'function',
            $functionData,
            'test_id'
        );

        $expected = [
            'type' => 'function',
            'function' => [
                'name' => 'test_name',
                'description' => 'desc',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'zipcode' => ['type' => 'string', 'description' => 'The zipcode to fetch weather for']
                    ],
                    'required' => ['zipcode'],
                    'additionalProperties' => false
                ]
            ],
            'id' => 'test_id'
        ];

        $this->assertEquals($expected, $toolCall->toArray());
    }

    public function testFromArray(): void
    {
        $data = [
            'type' => 'function',
            'function' => [
                'name' => 'test_name',
                'arguments' => 'args'
            ],
            'id' => 'test_id'
        ];

        $toolCall = ToolCall::fromArray($data);

        $this->assertEquals('function', $toolCall->type);
        $this->assertEquals('test_id', $toolCall->id);
        $this->assertInstanceOf(FunctionData::class, $toolCall->function);
        $this->assertEquals('test_name', $toolCall->function->name);
        $this->assertEquals('args', $toolCall->function->arguments);
    }

    public function testNullValues(): void
    {
        $toolCall = new ToolCall();

        $this->assertNull($toolCall->type);
        $this->assertNull($toolCall->function);
        $this->assertNull($toolCall->id);

        $this->assertEquals([], $toolCall->toArray());
    }

    public function testFromArrayWithNullFunction(): void
    {
        $data = [
            'type' => 'function',
            'function' => null,
            'id' => 'test_id'
        ];

        $toolCall = ToolCall::fromArray($data);

        $this->assertEquals('function', $toolCall->type);
        $this->assertNull($toolCall->function);
        $this->assertEquals('test_id', $toolCall->id);
    }
}
