<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests;

use fholbrook\Openrouter\OpenRouter;
use fholbrook\Openrouter\OpenRouterConfig;
use fholbrook\Openrouter\Resources\ChatResource;
use fholbrook\Openrouter\Resources\ModelsResource;
use PHPUnit\Framework\TestCase;

class OpenRouterTest extends TestCase
{
    public function testConstructorWiresResources(): void
    {
        $client = new OpenRouter(new OpenRouterConfig(apiKey: 'test-key'));

        $this->assertInstanceOf(ChatResource::class, $client->chat);
        $this->assertInstanceOf(ModelsResource::class, $client->models);
    }
}
