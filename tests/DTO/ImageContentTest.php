<?php

namespace fholbrook\Openrouter\Tests\DTO;

use fholbrook\Openrouter\DTO\ImageContent;
use fholbrook\Openrouter\Exceptions\OpenRouterException;
use PHPUnit\Framework\TestCase;

class ImageContentTest extends TestCase
{
    public function testConstructor()
    {
        $content = new ImageContent('http://example.com/image.jpg');
        $this->assertEquals('http://example.com/image.jpg', $content->url);
        $this->assertEquals('image_url', $content->type);
    }

    public function testFromFile()
    {
        // Create temp test file
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'test content');

        $content = ImageContent::fromFile($tmpFile);
        
        $this->assertStringStartsWith('data:text/plain;base64,', $content->url);
        
        unlink($tmpFile);
    }

    public function testFromFileThrowsExceptionForNonexistentFile()
    {
        $this->expectException(OpenRouterException::class);
        ImageContent::fromFile('nonexistent.jpg');
    }

    public function testFromContent()
    {
        $content = ImageContent::fromContent('test content');
        $this->assertStringStartsWith('data:text/plain;base64,', $content->url);
    }

    public function testToArray()
    {
        $content = new ImageContent('http://example.com/image.jpg');
        $array = $content->toArray();

        $this->assertEquals([
            'image_url' => ['url' => 'http://example.com/image.jpg'],
            'type' => 'image_url'
        ], $array);
    }

    public function testFromArray()
    {
        $array = [
            'image_url' => ['url' => 'http://example.com/image.jpg'],
            'type' => 'custom_type'
        ];

        $content = ImageContent::fromArray($array);

        $this->assertEquals('http://example.com/image.jpg', $content->url);
        $this->assertEquals('custom_type', $content->type);
    }

    public function testFromArrayWithDefaultType()
    {
        $array = [
            'image_url' => ['url' => 'http://example.com/image.jpg']
        ];

        $content = ImageContent::fromArray($array);

        $this->assertEquals('http://example.com/image.jpg', $content->url);
        $this->assertEquals('image_url', $content->type);
    }
}
