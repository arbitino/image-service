<?php

declare(strict_types=1);

namespace Tests\Feature;

use Arbitino\ImageService\DTO\ServiceConfig;
use Arbitino\ImageService\Manager\Manager;
use Arbitino\ImageService\Process;
use Arbitino\ImageService\Service;
use Arbitino\ImageService\Storage\Storage;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    private ?Manager $managerMock = null;
    private ?Storage $storageMock = null;

    protected function setUp(): void
    {
        $this->managerMock = $this->createMock(Manager::class);
        $this->storageMock = $this->createMock(Storage::class);
    }

    protected function tearDown(): void
    {
        $this->managerMock = null;
        $this->storageMock = null;
    }

    public function test_modify_with_array_params()
    {
        $path = '/path/to/image.jpg';
        $params = [
            'quality' => 90,
            'method' => 'resize',
            'format' => 'jpg',
            'resize' => '800x600',
            'extra' => [],
        ];

        $service = $this->getService();
        $process = $service->modify($path, $params);

        $this->assertInstanceOf(Process::class, $process);
    }

    public function test_modify_with_string_params()
    {
        $path = '/path/to/image.jpg';
        $params = 'r/800x600/f/jpg/q/90/m/resize/';

        $service = $this->getService();
        $process = $service->modify($path, $params);

        $this->assertInstanceOf(Process::class, $process);
    }

    public function test_get_modified_images()
    {
        $this->storageMock
            ->expects($this->once())
            ->method('getModifiedImages')
            ->willReturn([]);

        $service = $this->getService();
        $result = $service->getModifiedImagesByOriginal('');

        $this->assertEquals([], $result);
    }

    public function test_have_modified_images()
    {
        $this->storageMock
            ->expects($this->once())
            ->method('haveModifiedImages')
            ->willReturn(false);

        $service = $this->getService();
        $result = $service->imageHaveModifiedImages('');

        $this->assertFalse($result);
    }

    public function test_delete_modified_images()
    {
        $this->storageMock
            ->expects($this->exactly(2))
            ->method('deleteModifiedImages');

        $this->storageMock
            ->expects($this->once())
            ->method('delete');

        $service = $this->getService();
        $service->deleteModifiedImagesByOriginal('');
        $service->deleteModifiedImagesByOriginal('', false);
    }

    private function getService(array $config = []): Service
    {
        $serviceConfig = $config ? new ServiceConfig(...$config) : new ServiceConfig();

        return new Service(
            $this->managerMock,
            $this->storageMock,
            $serviceConfig,
        );
    }
}
