<?php

declare(strict_types=1);

namespace Tests\Unit\Storage;

use Arbitino\ImageService\Storage\FileSystemStorage;
use PHPUnit\Framework\TestCase;

class FileSystemStorageTest extends TestCase
{
    private string $testRoot;
    private string $fixturesDir;
    private FileSystemStorage $storage;

    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/../../fixtures';
        $this->testRoot = __DIR__ . '/test_storage';

        if (!file_exists($this->testRoot)) {
            mkdir($this->testRoot, 0755, true);
        }

        $this->storage = new FileSystemStorage($this->testRoot);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->testRoot);
    }

    public function test_make_directory(): void
    {
        $dir = '/test_dir';
        $this->assertFalse($this->storage->exists($dir));

        $result = $this->storage->makeDirectory($dir);
        $this->assertTrue($result);
        $this->assertTrue($this->storage->exists($dir));
    }

    public function test_exists(): void
    {
        $dir = '/test_exists_dir';
        $this->storage->makeDirectory($dir);

        $this->assertTrue($this->storage->exists($dir));
        $this->assertFalse($this->storage->exists('/non_existent_dir'));
    }

    public function test_delete(): void
    {
        $file = '/test_delete_file.txt';
        file_put_contents($this->testRoot . $file, 'test content');

        $this->assertTrue($this->storage->exists($file));

        $result = $this->storage->delete($file);
        $this->assertTrue($result);
        $this->assertFalse($this->storage->exists($file));

        $result = $this->storage->delete('/non_existent_file.txt');
        $this->assertTrue($result);
    }

    public function test_get_files(): void
    {
        $dir = '/test_files_dir';
        $this->storage->makeDirectory($dir);

        $file1 = $dir . '/file1.txt';
        $file2 = $dir . '/file2.txt';
        file_put_contents($this->testRoot . $file1, 'file1 content');
        file_put_contents($this->testRoot . $file2, 'file2 content');

        $files = $this->storage->getFiles($dir);
        $this->assertCount(2, $files);
        $this->assertContains($this->testRoot . $file1, $files);
        $this->assertContains($this->testRoot . $file2, $files);
    }

    public function test_get_files_recursive(): void
    {
        $dir = '/test_recursive_dir';
        $subDir = $dir . '/subdir';
        $this->storage->makeDirectory($dir);
        $this->storage->makeDirectory($subDir);

        $file1 = $dir . '/file1.txt';
        $file2 = $subDir . '/file2.txt';
        file_put_contents($this->testRoot . $file1, 'file1 content');
        file_put_contents($this->testRoot . $file2, 'file2 content');

        $files = $this->storage->getFilesRecursive($dir);
        $this->assertCount(2, $files);
        $this->assertContains($this->testRoot . $file1, $files);
        $this->assertContains($this->testRoot . $file2, $files);
    }

    public function test_url(): void
    {
        $path = $this->testRoot . '/some/file.txt';
        $expectedUrl = '/some/file.txt';

        $this->assertEquals($expectedUrl, $this->storage->url($path));
    }

    public function test_path(): void
    {
        $path = '/file.txt';
        $fullPath = $this->testRoot . '/file.txt';

        $this->assertEquals($fullPath, $this->storage->path($path));
    }

    public function test_image_params(): void
    {
        $file = '/test_image.jpg';
        copy($this->fixturesDir . '/test_image.jpg', $this->testRoot . $file);

        $params = $this->storage->imageParams($file);
        $this->assertArrayHasKey('path', $params);
        $this->assertArrayHasKey('url', $params);
        $this->assertArrayHasKey('dirname', $params);
        $this->assertArrayHasKey('basename', $params);
        $this->assertArrayHasKey('extension', $params);
        $this->assertArrayHasKey('filename', $params);
        $this->assertArrayHasKey('width', $params);
        $this->assertArrayHasKey('height', $params);
        $this->assertArrayHasKey('mime', $params);
    }

    public function test_get_modified_images(): void
    {
        $images = $this->createImages();

        $result = $this->storage->getModifiedImages($images[0]);

        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);
        $this->assertContains($images[1], $result);
        $this->assertContains($images[2], $result);
    }

    public function test_have_modified_images()
    {
        $images = $this->createImages();

        $result = $this->storage->haveModifiedImages($images[0]);

        $this->assertTrue($result);
    }

    public function test_delete_modified_images()
    {
        $images = $this->createImages();

        $this->assertTrue(file_exists($images[1]));
        $this->assertTrue(file_exists($images[2]));

        $this->storage->deleteModifiedImages($images[0]);

        $this->assertFalse(file_exists($images[1]));
        $this->assertFalse(file_exists($images[2]));
    }

    private function createImages(): array
    {
        $originalImagePath = $this->testRoot . '/image.jpg';
        $modifiedImagePath1 = $this->testRoot . '/webp/image.webp';
        $modifiedImagePath2 = $this->testRoot . '/webp/fit/image.webp';

        if (!file_exists($this->testRoot . '/webp')) {
            mkdir($this->testRoot . '/webp/', 0755, true);
        }

        if (!file_exists($this->testRoot . '/webp/fit')) {
            mkdir($this->testRoot . '/webp/fit', 0755, true);
        }

        touch($originalImagePath);
        touch($modifiedImagePath1);
        touch($modifiedImagePath2);

        return [
            $originalImagePath,
            $modifiedImagePath1,
            $modifiedImagePath2,
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
}
