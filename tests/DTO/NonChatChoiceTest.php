<?php

declare(strict_types=1);

namespace fholbrook\Openrouter\Tests\DTO;

use fholbrook\Openrouter\DTO\NonChatChoice;
use PHPUnit\Framework\TestCase;

class NonChatChoiceTest extends TestCase
{
    public function testCanCreateNonChatChoice(): void
    {
        $choice = new NonChatChoice('test text');
        
        $this->assertInstanceOf(NonChatChoice::class, $choice);
        $this->assertEquals('test text', $choice->text);
        $this->assertNull($choice->finishReason);
    }

    public function testCanCreateNonChatChoiceWithFinishReason(): void 
    {
        $choice = new NonChatChoice('test text', 'stop');
        
        $this->assertInstanceOf(NonChatChoice::class, $choice);
        $this->assertEquals('test text', $choice->text);
        $this->assertEquals('stop', $choice->finishReason);
    }
}
