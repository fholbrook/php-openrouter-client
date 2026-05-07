<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\DTO;

use fholbrook\Openrouter\DTO\Chat;
use fholbrook\Openrouter\DTO\ChatRequest;
use fholbrook\Openrouter\DTO\FunctionData;
use fholbrook\Openrouter\DTO\Message;
use fholbrook\Openrouter\DTO\ProviderPreferences;
use fholbrook\Openrouter\DTO\ResponseFormat;
use fholbrook\Openrouter\DTO\Schema;
use fholbrook\Openrouter\DTO\ToolCall;
use fholbrook\Openrouter\Enum\DataCollectionType;
use fholbrook\Openrouter\Enum\RoleType;
use PHPUnit\Framework\TestCase;

class ChatRequestTest extends TestCase
{
    public function testDefaultsIncludeMaxTokensOnly(): void
    {
        $request = new ChatRequest();
        $array = $request->toArray();

        $this->assertSame(['maxTokens' => 1024], $array);
    }

    public function testPromptIsIncludedWhenSet(): void
    {
        $request = new ChatRequest(
            prompt: 'Hello',
            model: 'anthropic/claude-3.5-sonnet',
        );

        $array = $request->toArray();

        $this->assertSame('Hello', $array['prompt']);
        $this->assertSame('anthropic/claude-3.5-sonnet', $array['model']);
        $this->assertArrayNotHasKey('messages', $array);
    }

    public function testChatWithMessagesOverridesPrompt(): void
    {
        $request = new ChatRequest(
            chat: new Chat(messages: [
                new Message(content: 'Hello', role: RoleType::USER->value),
            ]),
            prompt: 'should be ignored',
            model: 'anthropic/claude-3.5-sonnet',
        );

        $array = $request->toArray();

        $this->assertArrayNotHasKey('prompt', $array);
        $this->assertNull($request->prompt, 'prompt is nulled when chat has messages');
        $this->assertCount(1, $array['messages']);
        $this->assertSame('Hello', $array['messages'][0]['content']);
        $this->assertSame('user', $array['messages'][0]['role']);
    }

    public function testEmptyChatLeavesPromptUntouched(): void
    {
        $request = new ChatRequest(
            chat: new Chat(),
            prompt: 'fallback prompt',
        );

        $array = $request->toArray();

        $this->assertSame('fallback prompt', $array['prompt']);
        $this->assertArrayNotHasKey('messages', $array);
    }

    public function testNullsAreFilteredOut(): void
    {
        $request = new ChatRequest(
            prompt: 'hi',
            temperature: null,
            topP: null,
            seed: null,
        );

        $array = $request->toArray();

        $this->assertArrayNotHasKey('temperature', $array);
        $this->assertArrayNotHasKey('topP', $array);
        $this->assertArrayNotHasKey('seed', $array);
    }

    public function testToolsAreMappedToArrays(): void
    {
        $tool = new ToolCall(
            type: 'function',
            function: new FunctionData(
                name: 'weather',
                description: 'Look up weather',
                parameters: new Schema(),
            ),
        );

        $request = new ChatRequest(
            prompt: 'weather please',
            tools: [$tool],
            toolChoice: 'auto',
        );

        $array = $request->toArray();

        $this->assertSame('auto', $array['toolChoice']);
        $this->assertCount(1, $array['tools']);
        $this->assertSame('function', $array['tools'][0]['type']);
        $this->assertSame('weather', $array['tools'][0]['function']['name']);
    }

    public function testProviderAndResponseFormatAreNested(): void
    {
        $request = new ChatRequest(
            prompt: 'hi',
            responseFormat: new ResponseFormat(type: 'json_object'),
            provider: new ProviderPreferences(
                allowFallbacks: true,
                dataCollection: DataCollectionType::DENY,
            ),
        );

        $array = $request->toArray();

        $this->assertSame(['type' => 'json_object'], $array['responseFormat']);
        $this->assertSame(true, $array['provider']['allowFallbacks']);
        $this->assertSame(DataCollectionType::DENY, $array['provider']['dataCollection']);
    }

    public function testIncludeReasoningDefaultIsFilteredAsFalsy(): void
    {
        $request = new ChatRequest(prompt: 'hi');
        $this->assertArrayNotHasKey(
            'includeReasoning',
            $request->toArray(),
            'array_filter strips falsy includeReasoning default'
        );
    }

    public function testIncludeReasoningTrueIsKept(): void
    {
        $request = new ChatRequest(
            prompt: 'hi',
            includeReasoning: true,
        );
        $this->assertTrue($request->toArray()['includeReasoning']);
    }

    public function testStopAndModelsAndRoutePassThrough(): void
    {
        $request = new ChatRequest(
            prompt: 'hi',
            stop: ['STOP'],
            models: ['m1', 'm2'],
            route: 'fallback',
            transforms: ['middle-out'],
            logitBias: ['50256' => -100],
        );

        $array = $request->toArray();

        $this->assertSame(['STOP'], $array['stop']);
        $this->assertSame(['m1', 'm2'], $array['models']);
        $this->assertSame('fallback', $array['route']);
        $this->assertSame(['middle-out'], $array['transforms']);
        $this->assertSame(['50256' => -100], $array['logitBias']);
    }
}
