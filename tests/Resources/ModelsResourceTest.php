<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Resources;

use fholbrook\Openrouter\Exceptions\OpenRouterException;
use fholbrook\Openrouter\OpenRouterConfig;
use fholbrook\Openrouter\Resources\ModelsResource;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ModelsResourceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testListGetsModelsAndDecodesJson(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('get')
            ->once()
            ->with('models')
            ->andReturn(new Response(200, [], json_encode([
                'data' => [
                    ['id' => 'openai/gpt-4'],
                    ['id' => 'anthropic/claude-3.5-sonnet'],
                ],
            ])));

        $config = Mockery::mock(OpenRouterConfig::class);
        $config->shouldReceive('getClient')->andReturn($client);

        $result = (new ModelsResource($config))->list();

        $this->assertCount(2, $result['data']);
        $this->assertSame('openai/gpt-4', $result['data'][0]['id']);
    }

    public function testEndpointsHitsModelSpecificPath(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('get')
            ->once()
            ->with('models/openai/gpt-4/endpoints')
            ->andReturn(new Response(200, [], json_encode([
                'endpoints' => [['provider' => 'openai']],
            ])));

        $config = Mockery::mock(OpenRouterConfig::class);
        $config->shouldReceive('getClient')->andReturn($client);

        $result = (new ModelsResource($config))->endpoints('openai/gpt-4');

        $this->assertSame('openai', $result['endpoints'][0]['provider']);
    }

    public function testListWrapsThrownException(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('get')
            ->andThrow(new RequestException('boom', new Request('GET', 'models')));

        $config = Mockery::mock(OpenRouterConfig::class);
        $config->shouldReceive('getClient')->andReturn($client);

        try {
            (new ModelsResource($config))->list();
            $this->fail('Expected OpenRouterException');
        } catch (OpenRouterException $e) {
            $this->assertSame('boom', $e->getMessage());
            $this->assertInstanceOf(RequestException::class, $e->getPrevious());
        }
    }

    public function testEndpointsWrapsThrownException(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('get')
            ->andThrow(new RequestException('boom', new Request('GET', 'models/x/endpoints')));

        $config = Mockery::mock(OpenRouterConfig::class);
        $config->shouldReceive('getClient')->andReturn($client);

        $this->expectException(OpenRouterException::class);
        (new ModelsResource($config))->endpoints('x');
    }
}
