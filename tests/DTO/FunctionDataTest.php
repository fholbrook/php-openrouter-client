<?php

namespace fholbrook\Openrouter\Tests\DTO;

use fholbrook\Openrouter\DTO\FunctionData;
use fholbrook\Openrouter\DTO\Property;
use fholbrook\Openrouter\DTO\Schema;
use PHPUnit\Framework\TestCase;

class FunctionDataTest extends TestCase
{
    public function testConstructor()
    {
        $name = 'testFunction';
        $arguments = '{"arg1": "value1"}';
        $description = 'Test function description';
        $parameters = new Schema('object', [new Property('zipcode', 'string', 'The zipcode to fetch weather for')]);

        $functionData = new FunctionData($name, $arguments, $description, $parameters);

        $this->assertEquals($name, $functionData->name);
        $this->assertEquals($arguments, $functionData->arguments);
        $this->assertEquals($description, $functionData->description);
        $this->assertEquals($parameters, $functionData->parameters);
    }

    public function testConstructorWithMinimalParameters()
    {
        $name = 'testFunction';
        
        $functionData = new FunctionData($name);

        $this->assertEquals($name, $functionData->name);
        $this->assertNull($functionData->arguments);
        $this->assertNull($functionData->description);
        $this->assertNull($functionData->parameters);
    }

    public function testToArray()
    {
        $name = 'testFunction';
        $arguments = '{"arg1": "value1"}';
        $description = 'Test function description';
        $parameters = new Schema('object', [new Property('zipcode', 'string', 'The zipcode to fetch weather for')]);

        $functionData = new FunctionData($name, $arguments, $description, $parameters);

        $expected = [
            'name' => $name,
            'arguments' => $arguments,
            'description' => $description,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'zipcode' => ['type' => 'string', 'description' => 'The zipcode to fetch weather for']
                ],
                'required' => ['zipcode'],
                'additionalProperties' => false
            ],
        ];

        $this->assertEquals($expected, $functionData->toArray());
    }

    public function testFromArray()
    {
        $data = [
            'name' => 'testFunction',
            'arguments' => '{"arg1": "value1"}',
            'description' => 'Test function description',
            'parameters' =>  new Schema('object', [new Property('zipcode', 'string', 'The zipcode to fetch weather for')])
        ];

        $functionData = FunctionData::fromArray($data);

        $this->assertEquals($data['name'], $functionData->name);
        $this->assertEquals($data['arguments'], $functionData->arguments);
        $this->assertEquals($data['description'], $functionData->description);
        $this->assertEquals($data['parameters'], $functionData->parameters);
    }

    public function testToArrayWithNullParameters()
    {
        $functionData = new FunctionData('testFunction', description: 'no params');

        $this->assertSame(
            ['name' => 'testFunction', 'description' => 'no params'],
            $functionData->toArray()
        );
    }

    public function testFromArrayWithMinimalData()
    {
        $data = [
            'name' => 'testFunction'
        ];

        $functionData = FunctionData::fromArray($data);

        $this->assertEquals($data['name'], $functionData->name);
        $this->assertNull($functionData->arguments);
        $this->assertNull($functionData->description);
        $this->assertNull($functionData->parameters);
    }
}
