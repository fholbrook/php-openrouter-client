<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Resources;

use fholbrook\Openrouter\DTO\ChatRequest;
use fholbrook\Openrouter\DTO\ChatResponse;
use fholbrook\Openrouter\DTO\Message;
use fholbrook\Openrouter\Exceptions\OpenRouterException;
use fholbrook\Openrouter\OpenRouterConfig;
use fholbrook\Openrouter\Resources\ChatResource;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ChatResourceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function successResponse(): Response
    {
        return new Response(200, [], json_encode([
            'id' => 'gen-1',
            'model' => 'anthropic/claude-3.5-sonnet',
            'object' => 'chat.completion',
            'created' => 1700000000,
            'provider' => 'Anthropic',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Hello back!',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 5,
                'total_tokens' => 15,
            ],
        ]));
    }

    public function testChatPostsToCompletionsEndpointAndReturnsChatResponse(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')
            ->once()
            ->with('chat/completions', Mockery::on(function (array $options) {
                $this->assertArrayHasKey('json', $options);
                $this->assertSame('hello', $options['json']['prompt']);
                $this->assertSame('anthropic/claude-3.5-sonnet', $options['json']['model']);
                // verifies snake_case conversion happened
                $this->assertArrayHasKey('max_tokens', $options['json']);
                return true;
            }))
            ->andReturn($this->successResponse());

        $config = Mockery::mock(OpenRouterConfig::class);
        $config->shouldReceive('getClient')->andReturn($client);

        $resource = new ChatResource($config);
        $request = new ChatRequest(
            prompt: 'hello',
            model: 'anthropic/claude-3.5-sonnet',
        );

        $result = $resource->chat($request);

        $this->assertInstanceOf(ChatResponse::class, $result);
        $this->assertSame('gen-1', $result->getResponse()->id);
        $this->assertCount(1, $result->getResponse()->choices);
        $this->assertInstanceOf(Message::class, $result->getResponse()->choices[0]);
        $this->assertSame('Hello back!', $result->getResponse()->choices[0]->content);
    }

    public function testChatFallsBackToConfigDefaultModelWhenUnset(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')
            ->once()
            ->with('chat/completions', Mockery::on(function (array $options) {
                $this->assertSame('default/model', $options['json']['model']);
                return true;
            }))
            ->andReturn($this->successResponse());

        $config = Mockery::mock(OpenRouterConfig::class, ['k', 30, 'default/model'])
            ->makePartial();
        $config->shouldReceive('getClient')->andReturn($client);

        $resource = new ChatResource($config);
        $request = new ChatRequest(prompt: 'hi');

        $resource->chat($request);

        $this->assertSame('default/model', $request->model, 'Request is mutated with default model');
    }

    public function testChatThrowsOpenRouterExceptionWhenResponseContainsError(): void
    {
        $errorBody = json_encode([
            'error' => [
                'message' => 'Invalid API key',
                'code' => 401,
            ],
        ]);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')
            ->andReturn(new Response(200, [], $errorBody));

        $config = Mockery::mock(OpenRouterConfig::class);
        $config->shouldReceive('getClient')->andReturn($client);

        $resource = new ChatResource($config);

        try {
            $resource->chat(new ChatRequest(prompt: 'hi', model: 'm'));
            $this->fail('Expected OpenRouterException was not thrown');
        } catch (OpenRouterException $e) {
            $this->assertSame('Invalid API key', $e->getMessage());
            $this->assertSame(401, $e->getCode());
        }
    }

    public function testChatErrorPrefersMetadataRawWhenPresent(): void
    {
        $errorBody = json_encode([
            'error' => [
                'message' => 'fallback msg',
                'code' => 502,
                'metadata' => ['raw' => 'upstream provider failure'],
            ],
        ]);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')->andReturn(new Response(200, [], $errorBody));

        $config = Mockery::mock(OpenRouterConfig::class);
        $config->shouldReceive('getClient')->andReturn($client);

        try {
            (new ChatResource($config))->chat(new ChatRequest(prompt: 'hi', model: 'm'));
            $this->fail('Expected OpenRouterException');
        } catch (OpenRouterException $e) {
            $this->assertSame('upstream provider failure', $e->getMessage());
        }
    }

    public function testChatWrapsThrownGuzzleException(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post')
            ->andThrow(new RequestException(
                'connection refused',
                new Request('POST', 'chat/completions'),
            ));

        $config = Mockery::mock(OpenRouterConfig::class);
        $config->shouldReceive('getClient')->andReturn($client);

        $resource = new ChatResource($config);

        try {
            $resource->chat(new ChatRequest(prompt: 'hi', model: 'm'));
            $this->fail('Expected OpenRouterException');
        } catch (OpenRouterException $e) {
            $this->assertSame('connection refused', $e->getMessage());
            $this->assertInstanceOf(RequestException::class, $e->getPrevious());
        }
    }
}
