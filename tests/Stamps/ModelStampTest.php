<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Stamps;

use fholbrook\Openrouter\Stamps\ModelStamp;
use PHPUnit\Framework\TestCase;

class ModelStampTest extends TestCase
{
    public function testConstructorStoresModelName(): void
    {
        $stamp = new ModelStamp('anthropic/claude-3.5-sonnet');

        $this->assertSame('anthropic/claude-3.5-sonnet', $stamp->model);
    }

    public function testToArray(): void
    {
        $stamp = new ModelStamp('openai/gpt-4');

        $this->assertSame(['model' => 'openai/gpt-4'], $stamp->toArray());
    }

    public function testFromArrayRoundTrip(): void
    {
        $original = new ModelStamp('mistralai/mistral-7b-instruct');

        $restored = ModelStamp::fromArray($original->toArray());

        $this->assertInstanceOf(ModelStamp::class, $restored);
        $this->assertSame($original->model, $restored->model);
    }
}
