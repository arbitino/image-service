<?php

declare(strict_types=1);

namespace Tests\Unit\Manager;

use Arbitino\ImageService\Exceptions\ImageManagerException;
use Arbitino\ImageService\Manager\InterventionImageManager;
use Arbitino\ImageService\Manager\Manager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InterventionImageManagerTest extends TestCase
{
    private ?Manager $manager = null;
    private ?string $fixturesDir = null;
    private ?string $testRoot = null;

    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/../../fixtures';
        $this->testRoot = __DIR__ . '/test_storage';
        $this->manager = new InterventionImageManager();

        if (!file_exists($this->testRoot)) {
            mkdir($this->testRoot, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        $this->manager = null;

        $this->deleteDirectory($this->testRoot);
    }

    public function test_save_image()
    {
        $image = $this->getExistImagePath();
        $newImage = $this->newImagePath();

        $this->manager->makeImage($image);
        $this->manager->saveImage($newImage);

        $this->assertTrue(file_exists($newImage));
    }

    public function test_resize_image_by_resize_method()
    {
        $image = $this->getExistImagePath();
        $newImage = $this->newImagePath();

        $this->manager->makeImage($image);
        $this->manager->resizeImage('resize', 100, 110);
        $this->manager->saveImage($newImage);

        $this->assertTrue(file_exists($newImage));

        $sizes = getimagesize($newImage);

        $this->assertNotEmpty($sizes);
        $this->assertEquals(100, $sizes[0]);
        $this->assertEquals(110, $sizes[1]);
    }

    public function test_resize_image_by_crop_method()
    {
        $image = $this->getExistImagePath();
        $newImage = $this->newImagePath();

        $this->manager->makeImage($image);
        $this->manager->resizeImage('crop', 50, 50);
        $this->manager->saveImage($newImage);

        $this->assertTrue(file_exists($newImage));

        $sizes = getimagesize($newImage);

        $this->assertNotEmpty($sizes);
        $this->assertEquals(50, $sizes[0]);
        $this->assertEquals(50, $sizes[1]);
    }

    public function test_resize_image_by_fit_method()
    {
        $image = $this->getExistImagePath();
        $newImage = $this->newImagePath();

        $this->manager->makeImage($image);
        $this->manager->resizeImage('fit', 50, 50);
        $this->manager->saveImage($newImage);

        $this->assertTrue(file_exists($newImage));

        $sizes = getimagesize($newImage);

        $this->assertNotEmpty($sizes);
        $this->assertEquals(50, $sizes[0]);
        $this->assertEquals(50, $sizes[1]);
    }

    #[DataProvider('convertsMap')]
    public function test_convert_image(string $convertType, int $expected)
    {
        $image = $this->getExistImagePath();
        $newImage = $this->newImagePath($convertType);

        $this->manager->makeImage($image);
        $this->manager->convertImage($convertType);
        $this->manager->saveImage($newImage);

        $this->assertTrue(file_exists($newImage));

        $type = exif_imagetype($newImage);
        $this->assertEquals($expected, $type);
    }

    public function test_manager_convert_exception()
    {
        $this->expectException(ImageManagerException::class);

        $image = $this->getExistImagePath();

        $this->manager->makeImage($image);
        $this->manager->convertImage('mp3');
    }

    public function test_manager_resize_exception()
    {
        $this->expectException(ImageManagerException::class);

        $image = $this->getExistImagePath();

        $this->manager->makeImage($image);
        $this->manager->resizeImage('someCoolMethod', 500, 10);
    }

    public function test_manager_quality_less_one_exception()
    {
        $this->expectException(ImageManagerException::class);

        $image = $this->getExistImagePath();

        $this->manager->makeImage($image);
        $this->manager->setQuality(-22);
    }

    public function test_manager_quality_more_hundred_exception()
    {
        $this->expectException(ImageManagerException::class);

        $image = $this->getExistImagePath();

        $this->manager->makeImage($image);
        $this->manager->setQuality(120);
    }

    public static function convertsMap(): array
    {
        return [
            ['webp', IMAGETYPE_WEBP],
            ['gif', IMAGETYPE_GIF],
            ['png', IMAGETYPE_PNG],
            ['jpeg', IMAGETYPE_JPEG],
        ];
    }

    private function deleteDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            if (is_dir($dir . '/' . $file)) {
                $this->deleteDirectory($dir . '/' . $file);
            } else {
                unlink($dir . '/' . $file);
            }
        }

        rmdir($dir);
    }

    private function getExistImagePath(): string
    {
        $image = $this->testRoot . '/test_image.jpg';

        if (!file_exists($image)) {
            copy($this->fixturesDir . '/test_image.jpg', $image);
        }
        return $image;
    }

    private function newImagePath(string $format = 'jpg'): string
    {
        $newDir = $this->testRoot . '/new_dir';

        if (!file_exists($newDir)) {
            mkdir($newDir, 0755, true);
        }

        return $newDir . '/new_image.' . $format;
    }
}
