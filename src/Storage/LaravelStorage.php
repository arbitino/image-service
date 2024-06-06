<?php

declare(strict_types=1);

namespace Arbitino\ImageService\Storage;

use Illuminate\Contracts\Filesystem\Filesystem;

final readonly class LaravelStorage implements Storage
{
    private Filesystem $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    public function makeDirectory(string $dir, int $permissions = 0755): bool
    {
        return $this->storage->makeDirectory($dir);
    }

    public function exists(string $path): bool
    {
        return $this->storage->exists($path);
    }

    public function delete(string $path): bool
    {
        return $this->storage->delete($path);
    }

    /**
     * @param string $dir
     * @return string[]
     */
    public function getFiles(string $dir): array
    {
        return $this->storage->files($dir);
    }

    /**
     * @param string $dir
     * @return string[]
     */
    public function getFilesRecursive(string $dir): array
    {
        return $this->storage->files($dir, true);
    }

    public function url(string $path): string
    {
        // @phpstan-ignore-next-line
        return $this->storage->url($path);
    }

    public function path(string $path): string
    {
        return $this->storage->path($path);
    }

    /**
     * @param string $path
     * @return array{
     *       path: string,
     *       url: string,
     *       width: int,
     *       height: int,
     *       mime: string,
     *       dirname: string,
     *       basename: string,
     *       extension: string,
     *       filename: string
     *   }
     */
    public function imageParams(string $path): array
    {
        $path = $this->path($path);
        $sizeParams = getimagesize($path);

        return [
            'path' => $path,
            'url' => $this->url($path),
            'width' => $sizeParams[0],
            'height' => $sizeParams[1],
            'mime' => $sizeParams['mime'],
            ...pathinfo($path),
        ];
    }

    public function getModifiedImages(string $path): array
    {
        $images = [];
        $originalPathInfo = pathinfo($path);
        $originPath = ltrim($originalPathInfo['dirname'] . '/' . $originalPathInfo['basename'], '/');
        $files = $this->getFiles($originalPathInfo['dirname']);

        foreach ($files as $file) {
            $pathInfo = pathinfo($file);
            $path = $pathInfo['dirname'] . '/' . $pathInfo['basename'];

            if (
                $pathInfo['filename'] === $originalPathInfo['filename']
                && ltrim($path, '/') !== $originPath
            ) {
                $images[] = $path;
            }
        }

        return $images;
    }

    public function haveModifiedImages(string $path): bool
    {
        $originalPathInfo = pathinfo($path);
        $originPath = ltrim($originalPathInfo['dirname'] . '/' . $originalPathInfo['basename'], '/');
        $files = $this->getFiles($originalPathInfo['dirname']);

        foreach ($files as $file) {
            $pathInfo = pathinfo($file);
            $path = $pathInfo['dirname'] . '/' . $pathInfo['basename'];

            if (
                $pathInfo['filename'] === $originalPathInfo['filename']
                && ltrim($path, '/') !== $originPath
            ) {
                return true;
            }
        }

        return false;
    }

    public function deleteModifiedImages(string $path): void
    {
        $images = $this->getModifiedImages($path);

        foreach ($images as $image) {
            $this->delete($image);
        }
    }
}
