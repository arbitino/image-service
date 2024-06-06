<?php

declare(strict_types=1);

namespace Tests\Unit\Storage;

use Arbitino\ImageService\Storage\LaravelStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class LaravelStorageTest extends TestCase
{
    private Filesystem $storageMock;
    private LaravelStorage $laravelStorage;

    protected function setUp(): void
    {
        $this->storageMock = $this->createMock(Filesystem::class);
        $this->laravelStorage = new LaravelStorage($this->storageMock);
    }

    public function test_make_directory(): void
    {
        $dir = 'test_dir';
        // @phpstan-ignore-next-line
        $this->storageMock->method('makeDirectory')->with($dir)->willReturn(true);

        $result = $this->laravelStorage->makeDirectory($dir);
        $this->assertTrue($result);
    }

    public function test_exists(): void
    {
        $path = 'test_exists_file.txt';
        // @phpstan-ignore-next-line
        $this->storageMock->method('exists')->with($path)->willReturn(true);

        $result = $this->laravelStorage->exists($path);
        $this->assertTrue($result);
    }

    public function test_delete(): void
    {
        $path = 'test_delete_file.txt';
        // @phpstan-ignore-next-line
        $this->storageMock->method('delete')->with($path)->willReturn(true);

        $result = $this->laravelStorage->delete($path);
        $this->assertTrue($result);
    }

    public function test_get_files(): void
    {
        $dir = 'test_dir';
        $files = ['file1.txt', 'file2.txt'];
        // @phpstan-ignore-next-line
        $this->storageMock->method('files')->with($dir)->willReturn($files);

        $result = $this->laravelStorage->getFiles($dir);
        $this->assertEquals($files, $result);
    }

    public function test_get_files_recursive(): void
    {
        $dir = 'test_dir';
        $files = ['file1.txt', 'subdir/file2.txt'];
        // @phpstan-ignore-next-line
        $this->storageMock->method('files')->with($dir, true)->willReturn($files);

        $result = $this->laravelStorage->getFilesRecursive($dir);
        $this->assertEquals($files, $result);
    }

    public function test_path(): void
    {
        $path = 'file.txt';
        $fullPath = '/storage/file.txt';
        // @phpstan-ignore-next-line
        $this->storageMock->method('path')->with($path)->willReturn($fullPath);

        $result = $this->laravelStorage->path($path);
        $this->assertEquals($fullPath, $result);
    }
}
