<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Stamps;

use DateTimeImmutable;
use fholbrook\Openrouter\Stamps\CreatedAtStamp;
use PHPUnit\Framework\TestCase;

class CreatedAtStampTest extends TestCase
{
    public function testConstructorStoresDateTime(): void
    {
        $dt = new DateTimeImmutable('2024-01-02T03:04:05+00:00');
        $stamp = new CreatedAtStamp($dt);

        $this->assertSame($dt, $stamp->createdAt);
    }

    public function testToArrayProducesRfc3339(): void
    {
        $dt = new DateTimeImmutable('2024-01-02T03:04:05+00:00');
        $stamp = new CreatedAtStamp($dt);

        $this->assertSame(
            ['createdAt' => '2024-01-02T03:04:05+00:00'],
            $stamp->toArray()
        );
    }

    public function testFromArrayRoundTrip(): void
    {
        $original = new CreatedAtStamp(new DateTimeImmutable('2024-01-02T03:04:05+00:00'));

        $restored = CreatedAtStamp::fromArray($original->toArray());

        $this->assertInstanceOf(CreatedAtStamp::class, $restored);
        $this->assertEquals(
            $original->createdAt->getTimestamp(),
            $restored->createdAt->getTimestamp()
        );
    }
}
