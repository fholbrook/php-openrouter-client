<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Serializer;

use fholbrook\Openrouter\Exceptions\OpenRouterException;
use fholbrook\Openrouter\Serializer\OpenRouterSerializer;
use PHPUnit\Framework\TestCase;
use stdClass;

class OpenRouterSerializerTest extends TestCase
{
    public function testToArraySnakeCasesKeys(): void
    {
        $object = new class {
            public function toArray(): array
            {
                return [
                    'maxTokens' => 1024,
                    'topP' => 0.9,
                    'frequencyPenalty' => 0.5,
                ];
            }
        };

        $this->assertSame(
            [
                'max_tokens' => 1024,
                'top_p' => 0.9,
                'frequency_penalty' => 0.5,
            ],
            OpenRouterSerializer::toArray($object)
        );
    }

    public function testToArrayThrowsWhenNoToArrayMethod(): void
    {
        $this->expectException(OpenRouterException::class);
        $this->expectExceptionMessage('toArray');

        OpenRouterSerializer::toArray(new stdClass());
    }

    public function testFromArrayThrowsWhenClassDoesNotExist(): void
    {
        $this->expectException(OpenRouterException::class);

        OpenRouterSerializer::fromArray(['a' => 1], 'NonExistent\\Klass');
    }

    public function testFromArrayThrowsWhenClassMissingFromArray(): void
    {
        $this->expectException(OpenRouterException::class);

        OpenRouterSerializer::fromArray(['a' => 1], stdClass::class);
    }

    public function testArrayToCamelCaseRecursesIntoNestedArrays(): void
    {
        $input = [
            'max_tokens' => 50,
            'response_format' => [
                'type' => 'json_object',
                'json_schema' => [
                    'some_inner_key' => 'untouched',
                ],
            ],
        ];

        $result = OpenRouterSerializer::arrayToCamelCase($input);

        $this->assertSame(50, $result['maxTokens']);
        $this->assertSame('json_object', $result['responseFormat']['type']);
        // json_schema → jsonSchema (key converted), but value pass-through
        $this->assertArrayHasKey('jsonSchema', $result['responseFormat']);
        $this->assertSame(
            ['some_inner_key' => 'untouched'],
            $result['responseFormat']['jsonSchema'],
            'jsonSchema value contents must NOT be converted'
        );
    }

    public function testArrayToSnakeCaseRecursesIntoNestedArrays(): void
    {
        $input = [
            'maxTokens' => 50,
            'responseFormat' => [
                'type' => 'json_object',
                'jsonSchema' => [
                    'someInnerKey' => 'untouched',
                ],
            ],
        ];

        $result = OpenRouterSerializer::arrayToSnakeCase($input);

        $this->assertSame(50, $result['max_tokens']);
        $this->assertSame('json_object', $result['response_format']['type']);
        $this->assertArrayHasKey('json_schema', $result['response_format']);
        $this->assertSame(
            ['someInnerKey' => 'untouched'],
            $result['response_format']['json_schema'],
            'json_schema value contents must NOT be converted'
        );
    }

    public function testParametersKeyValueIsNotRecursed(): void
    {
        $input = [
            'parameters' => [
                'someInnerKey' => 'left alone',
            ],
        ];

        $snake = OpenRouterSerializer::arrayToSnakeCase($input);
        $this->assertSame(['someInnerKey' => 'left alone'], $snake['parameters']);

        $camel = OpenRouterSerializer::arrayToCamelCase([
            'parameters' => ['some_inner_key' => 'left alone'],
        ]);
        $this->assertSame(['some_inner_key' => 'left alone'], $camel['parameters']);
    }

    public function testSerializeProducesSnakeCasedJson(): void
    {
        $object = new class {
            public function toArray(): array
            {
                return ['maxTokens' => 100];
            }
        };

        $this->assertSame('{"max_tokens":100}', OpenRouterSerializer::serialize($object));
    }

    public function testDeserializeRoundTrip(): void
    {
        $json = '{"id":"abc","messages":[{"role":"user","content":"Hi"}]}';

        $chat = OpenRouterSerializer::deserialize($json, \fholbrook\Openrouter\DTO\Chat::class);

        $this->assertInstanceOf(\fholbrook\Openrouter\DTO\Chat::class, $chat);
        $this->assertSame('abc', $chat->id);
        $this->assertCount(1, $chat->messages);
        $this->assertSame('user', $chat->messages[0]->role);
    }

    public function testIntegerKeysArePreserved(): void
    {
        $input = [
            'logit_bias' => [
                '50256' => -100,
            ],
        ];
        $result = OpenRouterSerializer::arrayToCamelCase($input);
        // PHP coerces numeric string keys to int when set; check value is preserved
        $this->assertSame(-100, $result['logitBias'][50256]);
    }
}
