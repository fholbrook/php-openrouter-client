<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Resources;

use fholbrook\Openrouter\Exceptions\OpenRouterException;
use fholbrook\Openrouter\OpenRouterConfig;
use fholbrook\Openrouter\Resources\AbstractResource;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AbstractResourceTest extends TestCase
{
    private function makeHarness(): object
    {
        $config = new OpenRouterConfig(apiKey: 'k');

        return new class($config) extends AbstractResource {
            public function callJsonDecode(?Response $response): mixed
            {
                return $this->jsonDecode($response);
            }

            public function callParseError(array $data): OpenRouterException
            {
                return $this->parseError($data);
            }
        };
    }

    public function testJsonDecodeReturnsNullForNullResponse(): void
    {
        $this->assertNull($this->makeHarness()->callJsonDecode(null));
    }

    public function testJsonDecodeParsesResponseBody(): void
    {
        $response = new Response(200, [], json_encode(['hello' => 'world']));

        $this->assertSame(
            ['hello' => 'world'],
            $this->makeHarness()->callJsonDecode($response)
        );
    }

    public function testParseErrorPrefersMetadataRaw(): void
    {
        $exception = $this->makeHarness()->callParseError([
            'message' => 'fallback message',
            'code' => 401,
            'metadata' => ['raw' => 'upstream raw error'],
        ]);

        $this->assertInstanceOf(OpenRouterException::class, $exception);
        $this->assertSame('upstream raw error', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
    }

    public function testParseErrorFallsBackToMessage(): void
    {
        $exception = $this->makeHarness()->callParseError([
            'message' => 'Bad request',
            'code' => 400,
        ]);

        $this->assertSame('Bad request', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
    }
}
