<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests;

use fholbrook\Openrouter\OpenRouterConfig;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class OpenRouterConfigTest extends TestCase
{
    public function testGetClientReturnsGuzzleClient(): void
    {
        $config = new OpenRouterConfig(apiKey: 'test-key');

        $this->assertInstanceOf(Client::class, $config->getClient());
    }

    public function testDefaultBaseUriIsOpenRouter(): void
    {
        $config = new OpenRouterConfig(apiKey: 'test-key');

        $client = $config->getClient();
        $baseUri = (string) $client->getConfig('base_uri');

        $this->assertSame('https://openrouter.ai/api/v1/', $baseUri);
    }

    public function testBaseUriIsNormalizedWithTrailingSlash(): void
    {
        $config = new OpenRouterConfig(
            apiKey: 'test-key',
            baseUri: 'https://example.com/api',
        );

        $client = $config->getClient();
        $baseUri = (string) $client->getConfig('base_uri');

        $this->assertSame('https://example.com/api/', $baseUri);
    }

    public function testTrailingSlashOnInputIsNotDoubled(): void
    {
        $config = new OpenRouterConfig(
            apiKey: 'test-key',
            baseUri: 'https://example.com/api/',
        );

        $client = $config->getClient();
        $baseUri = (string) $client->getConfig('base_uri');

        $this->assertSame('https://example.com/api/', $baseUri);
    }

    public function testTimeoutPropagated(): void
    {
        $config = new OpenRouterConfig(apiKey: 'k', timeout: 90);

        $this->assertSame(90, $config->getClient()->getConfig('timeout'));
    }

    public function testAuthorizationHeaderUsesApiKey(): void
    {
        $config = new OpenRouterConfig(apiKey: 'sk-abc123');

        $headers = $config->getClient()->getConfig('headers');

        $this->assertSame('Bearer sk-abc123', $headers['Authorization']);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testNullReferrerHeadersAreFiltered(): void
    {
        $config = new OpenRouterConfig(apiKey: 'k');

        $headers = $config->getClient()->getConfig('headers');

        $this->assertArrayNotHasKey('HTTP-Referer', $headers);
        $this->assertArrayNotHasKey('X-Title', $headers);
    }

    public function testReferrerHeadersIncludedWhenProvided(): void
    {
        $config = new OpenRouterConfig(
            apiKey: 'k',
            referrerUri: 'https://my-app.test',
            referrerTitle: 'My App',
        );

        $headers = $config->getClient()->getConfig('headers');

        $this->assertSame('https://my-app.test', $headers['HTTP-Referer']);
        $this->assertSame('My App', $headers['X-Title']);
    }

    public function testDefaultModelExposedAsProperty(): void
    {
        $config = new OpenRouterConfig(
            apiKey: 'k',
            defaultModel: 'openai/gpt-4',
        );

        $this->assertSame('openai/gpt-4', $config->defaultModel);
    }

    public function testHandlerStackIsAttached(): void
    {
        $config = new OpenRouterConfig(apiKey: 'k');

        $this->assertNotNull($config->getClient()->getConfig('handler'));
    }
}
