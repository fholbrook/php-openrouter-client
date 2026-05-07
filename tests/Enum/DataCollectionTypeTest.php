<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Enum;

use fholbrook\Openrouter\Enum\DataCollectionType;
use PHPUnit\Framework\TestCase;

class DataCollectionTypeTest extends TestCase
{
    public function testCaseValues(): void
    {
        $this->assertSame('allow', DataCollectionType::ALLOW->value);
        $this->assertSame('deny', DataCollectionType::DENY->value);
    }

    public function testFromValue(): void
    {
        $this->assertSame(DataCollectionType::ALLOW, DataCollectionType::from('allow'));
        $this->assertSame(DataCollectionType::DENY, DataCollectionType::from('deny'));
    }
}
