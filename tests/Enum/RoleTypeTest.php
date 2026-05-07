<?php
declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\Enum;

use fholbrook\Openrouter\Enum\RoleType;
use PHPUnit\Framework\TestCase;

class RoleTypeTest extends TestCase
{
    public function testCaseValues(): void
    {
        $this->assertSame('user', RoleType::USER->value);
        $this->assertSame('assistant', RoleType::ASSISTANT->value);
        $this->assertSame('system', RoleType::SYSTEM->value);
        $this->assertSame('function', RoleType::FUNCTION->value);
        $this->assertSame('tool', RoleType::TOOL->value);
    }

    public function testFromValue(): void
    {
        $this->assertSame(RoleType::USER, RoleType::from('user'));
        $this->assertSame(RoleType::ASSISTANT, RoleType::from('assistant'));
    }
}
