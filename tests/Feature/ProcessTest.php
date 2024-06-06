<?php

declare(strict_types=1);

namespace Tests\Feature;

use Arbitino\ImageService\Config\ImageConfig;
use Arbitino\ImageService\DTO\Image;
use Arbitino\ImageService\DTO\ProcessResult;
use Arbitino\ImageService\Manager\Manager;
use Arbitino\ImageService\Process;
use Arbitino\ImageService\Storage\Storage;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    private ?Manager $managerMock = null;
    private ?Storage $storageMock = null;
    private ?ImageConfig $configMock = null;
    private ?array $imageManagerParams = null;

    protected function setUp(): void
    {
        $this->managerMock = $this->createMock(Manager::class);
        $this->storageMock = $this->createMock(Storage::class);
        $this->configMock = $this->createMock(ImageConfig::class);

        $this->configMock
            ->method('getImage')
            ->willReturn($this->getImage());

        $this->imageManagerParams = [];
    }

    protected function tearDown(): void
    {
        $this->managerMock = null;
        $this->storageMock = null;
        $this->configMock = null;
    }

    public function test_set_quality(): void
    {
        $quality = 90;

        $this->configMock->expects($this->once())
            ->method('setQuality')
            ->with($quality);

        $process = new Process($this->managerMock, $this->storageMock, $this->configMock, $this->imageManagerParams);
        $result = $process->setQuality($quality);

        $this->assertSame($process, $result);
    }

    public function test_set_format(): void
    {
        $format = 'jpg';

        $this->configMock->expects($this->once())
            ->method('setFormat')
            ->with($format);

        $process = new Process($this->managerMock, $this->storageMock, $this->configMock, $this->imageManagerParams);
        $result = $process->setFormat($format);

        $this->assertSame($process, $result);
    }

    public function test_set_sizes(): void
    {
        $sizes = [800, 600];

        $this->configMock->expects($this->once())
            ->method('setSize')
            ->with($sizes);

        $process = new Process($this->managerMock, $this->storageMock, $this->configMock, $this->imageManagerParams);
        $result = $process->setSizes($sizes);

        $this->assertSame($process, $result);
    }

    public function test_set_method(): void
    {
        $method = 'resize';

        $this->configMock->expects($this->once())
            ->method('setMethod')
            ->with($method);

        $process = new Process($this->managerMock, $this->storageMock, $this->configMock, $this->imageManagerParams);
        $result = $process->setMethod($method);

        $this->assertSame($process, $result);
    }

    public function test_get_params(): void
    {
        $method = 'resize';
        $format = 'jpg';
        $quality = 90;
        $size = [800, 600];

        $this->configMock->method('getMethod')->willReturn($method);
        $this->configMock->method('getFormat')->willReturn($format);
        $this->configMock->method('getQuality')->willReturn($quality);
        $this->configMock->method('getSize')->willReturn($size);

        $process = new Process($this->managerMock, $this->storageMock, $this->configMock, $this->imageManagerParams);
        $params = $process->getParams();

        $expected = [
            'method' => $method,
            'format' => $format,
            'quality' => $quality,
            'size' => $size,
        ];

        $this->assertSame($expected, $params);
    }

    public function test_process(): void
    {
        $this->configMock->method('getPath')->willReturn('/path/to/image.jpg');
        $this->configMock->method('getQuality')->willReturn(90);
        $this->configMock->method('isResize')->willReturn(false);
        $this->configMock->method('isConvert')->willReturn(false);
        $this->configMock->method('getFormat')->willReturn('jpg');
        $this->configMock->method('getSize')->willReturn([800, 600]);
        $this->configMock->method('getMethod')->willReturn('resize');

        $this->storageMock->method('exists')->willReturn(false);
        $this->storageMock->method('makeDirectory')->willReturn(true);
        $this->storageMock->method('path')->willReturn('/path/to/new_image.jpg');
        $this->storageMock->method('imageParams')->willReturn([
            'path' => '/path/to/new_image.jpg',
            'url' => 'http://example.com/new_image.jpg',
            'width' => 800,
            'height' => 600,
            'mime' => 'image/jpeg',
            'dirname' => '/path/to',
            'basename' => 'new_image.jpg',
            'extension' => 'jpg',
            'filename' => 'new_image',
        ]);

        $process = new Process($this->managerMock, $this->storageMock, $this->configMock, $this->imageManagerParams);

        $this->managerMock->expects($this->once())
            ->method('makeImage')
            ->with('/path/to/image.jpg');

        $this->managerMock->expects($this->once())
            ->method('setQuality')
            ->with(90);

        $this->managerMock->expects($this->once())
            ->method('saveImage')
            ->with('/path/to/new_image.jpg');

        $result = $process->process();

        $this->assertInstanceOf(ProcessResult::class, $result);
        $this->assertInstanceOf(Image::class, $result->new);
        $this->assertInstanceOf(Image::class, $result->original);
    }

    private function getImage(): Image
    {
        return new Image([
            'dirname' => '/images',
            'basename' => 'image.jpg',
            'filename' => 'image',
            'extension' => 'jpg',
            'path' => '/images/image.jpg',
        ]);
    }
}
