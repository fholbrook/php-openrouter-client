<?php

namespace fholbrook\Openrouter\Tests\DTO;

use fholbrook\Openrouter\DTO\ProviderPreferences;
use fholbrook\Openrouter\Enum\DataCollectionType;
use PHPUnit\Framework\TestCase;

class ProviderPreferencesTest extends TestCase
{
    public function testToArrayWithAllPropertiesSet()
    {
        $preferences = new ProviderPreferences(
            allowFallbacks: true,
            requireParameters: false,
            dataCollection: DataCollectionType::ALLOW,
            order: ['OpenAI', 'Anthropic']
        );

        $expected = [
            'allowFallbacks' => true,
            'requireParameters' => false,
            'dataCollection' => DataCollectionType::ALLOW,
            'order' => ['OpenAI', 'Anthropic']
        ];

        $this->assertEquals($expected, $preferences->toArray());
    }

    public function testToArrayWithNullProperties()
    {
        $preferences = new ProviderPreferences();

        $this->assertEquals([], $preferences->toArray());
    }

    public function testToArrayWithSomePropertiesSet()
    {
        $preferences = new ProviderPreferences(
            allowFallbacks: true,
            order: ['OpenAI']
        );

        $expected = [
            'allowFallbacks' => true,
            'order' => ['OpenAI']
        ];

        $this->assertEquals($expected, $preferences->toArray());
    }
}
