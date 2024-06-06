<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Arbitino\ImageService\Config\ValidatedImageConfig;
use Arbitino\ImageService\DTO\Image;
use Arbitino\ImageService\DTO\ServiceConfig;
use Arbitino\ImageService\Exceptions\ImageConfigValidateException;
use PHPUnit\Framework\TestCase;

class ValidatedImageConfigTest extends TestCase
{
    public function test_constructor_and_getters()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image([]), [
            'quality' => 90,
            'method' => 'resize',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $this->assertEquals(90, $config->getQuality());
        $this->assertEquals('resize', $config->getMethod());
        $this->assertEquals('jpeg', $config->getFormat());
        $this->assertEquals([800, 600], $config->getSize());
    }

    public function test_set_quality()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image([]), [
            'quality' => 90,
            'method' => 'resize',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $config->setQuality(80);
        $this->assertEquals(80, $config->getQuality());
    }

    public function test_set_invalid_quality()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image([]), [
            'quality' => 90,
            'method' => 'resize',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $config->setQuality(150);
        $this->assertEquals(100, $config->getQuality());

        $config->setQuality(-10);
        $this->assertEquals(1, $config->getQuality());
    }

    public function test_set_default_quality()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image([]), [
            'method' => 'resize',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $this->assertEquals($config->getDefaultQuality(), $config->getQuality());
    }

    public function test_set_default_method()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image([]), [
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $this->assertEquals($config->getDefaultMethod(), $config->getMethod());
    }

    public function test_is_convert()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image(['extension' => 'jpg']), [
            'format' => 'webp',
            'resize' => '800x600',
        ]);

        $this->assertTrue($config->isConvert());
    }

    public function test_is_resize()
    {
        $config = new ValidatedImageConfig(
            new ServiceConfig(),
            new Image(['width' => 100, 'height' => 100]),
            [
                'format' => 'webp',
                'resize' => '800x600',
            ],
        );

        $this->assertTrue($config->isResize());
    }

    public function test_get_image_path()
    {
        $expectedPath = 'path/to/image.jpg';

        $config = new ValidatedImageConfig(
            new ServiceConfig(),
            new Image(['path' => $expectedPath]),
            [
                'format' => 'webp',
                'resize' => '800x600',
            ],
        );

        $this->assertEquals($expectedPath, $config->getPath());
    }

    public function test_get_image()
    {
        $image = new Image([]);
        $config = new ValidatedImageConfig(new ServiceConfig(), $image, [
            'format' => 'webp',
            'resize' => '800x600',
        ]);

        $this->assertEquals($image, $config->getImage());
    }

    public function test_set_size()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image([]), [
            'quality' => 90,
            'method' => 'resize',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $config->setSize('1024x768');
        $this->assertEquals([1024, 768], $config->getSize());
    }

    public function test_no_convert()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image(['extension' => 'jpeg']), [
            'format' => 'jpeg',
        ]);

        $this->assertFalse($config->isConvert());

        $config = new ValidatedImageConfig(new ServiceConfig(), new Image(['extension' => 'jpg']), [
            'format' => 'jpeg',
        ]);

        $this->assertFalse($config->isConvert());

        $config = new ValidatedImageConfig(new ServiceConfig(), new Image(['extension' => 'jpeg']), [
            'format' => 'jpg',
        ]);

        $this->assertFalse($config->isConvert());
    }

    public function test_set_invalid_size_array()
    {
        $this->expectException(ImageConfigValidateException::class);

        $config = new ValidatedImageConfig(new ServiceConfig(allowSizes: ['800x600']), new Image([]), [
            'quality' => 90,
            'method' => 'resize',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $config->setSize('1024x768');
    }

    public function test_set_invalid_size()
    {
        $this->expectException(ImageConfigValidateException::class);

        $config = new ValidatedImageConfig(new ServiceConfig(allowSizes: ['no-sizes']), new Image([]), [
            'quality' => 90,
            'method' => 'resize',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $config->setSize('1024x768');
    }

    public function test_set_method()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image([]), [
            'quality' => 90,
            'method' => 'resize',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);


        $config->setMethod('crop');
        $this->assertEquals('crop', $config->getMethod());
    }

    /**
     * @throws ImageConfigValidateException
     */
    public function test_set_invalid_method()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(allowMethods: ['crop']), new Image([]), [
            'quality' => 90,
            'method' => 'crop',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $this->expectException(ImageConfigValidateException::class);
        $config->setMethod('invalid_method');
    }

    public function test_set_format()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(), new Image([]), [
            'quality' => 90,
            'method' => 'crop',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $config->setFormat('png');
        $this->assertEquals('png', $config->getFormat());
    }

    public function test_set_invalid_format()
    {
        $config = new ValidatedImageConfig(new ServiceConfig(allowFormats: ['jpeg']), new Image([]), [
            'quality' => 90,
            'method' => 'crop',
            'format' => 'jpeg',
            'resize' => '800x600',
        ]);

        $this->expectException(ImageConfigValidateException::class);
        $config->setFormat('gif');
    }
}
