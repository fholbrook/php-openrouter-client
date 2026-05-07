<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Exceptions;

use fholbrook\Openrouter\Exceptions\OpenRouterException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class OpenRouterExceptionTest extends TestCase
{
    public function testExtendsException(): void
    {
        $exception = new OpenRouterException('boom', 42);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame('boom', $exception->getMessage());
        $this->assertSame(42, $exception->getCode());
    }

    public function testCarriesPreviousException(): void
    {
        $previous = new RuntimeException('underlying');
        $exception = new OpenRouterException('wrapper', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
