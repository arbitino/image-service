<?php

declare(strict_types=1);

namespace Arbitino\ImageService\Storage;

use DirectoryIterator;

final readonly class FileSystemStorage implements Storage
{
    public function __construct(
        private string $root,
    ) {}

    public function makeDirectory(string $dir, int $permissions = 0755): bool
    {
        $dir = $this->withRoot($dir);

        if ($this->exists($dir)) {
            return true;
        }

        return mkdir($dir, $permissions, true);
    }

    public function exists(string $path): bool
    {
        return file_exists($this->withRoot($path));
    }

    public function delete(string $path): bool
    {
        $path = $this->withRoot($path);

        if (!$this->exists($path)) {
            return true;
        }

        return unlink($path);
    }

    public function getFiles(string $dir): array
    {
        $files = [];
        $dir = $this->withRoot($dir);

        if (!$this->exists($dir)) {
            return [];
        }

        foreach (new DirectoryIterator($dir) as $file) {
            /** @var DirectoryIterator $file */
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    public function getFilesRecursive(string $dir): array
    {
        $files = [];
        $this->collectFilesRecursive($this->withRoot($dir), $files);
        return $files;
    }

    public function url(string $path): string
    {
        return $this->withoutRoot($path);
    }

    public function withRoot(string $path): string
    {
        if ($this->pathHasRoot($path)) {
            return $path;
        }

        return $this->replaceSlash($this->root . $path);
    }

    public function withoutRoot(string $path): string
    {
        if (!$this->pathHasRoot($path)) {
            return $path;
        }

        return $this->replaceSlash(str_replace($this->root, '', $path));
    }

    public function path(string $path): string
    {
        return $this->withRoot($path);
    }

    public function imageParams(string $path): array
    {
        $path = $this->withRoot($path);
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

    /**
     * @param string $dir
     * @param string[] $files
     * @return void
     */
    private function collectFilesRecursive(string $dir, array &$files): void
    {
        if (!$this->exists($dir)) {
            return;
        }

        foreach (new DirectoryIterator($dir) as $file) {
            /** @var DirectoryIterator $file */
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                $this->collectFilesRecursive($file->getPathname(), $files);
            } elseif ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
    }

    private function pathHasRoot(string $path): bool
    {
        return str_contains($this->replaceSlash($path), $this->replaceSlash($this->root));
    }

    private function replaceSlash(string $path): string
    {
        return str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
    }

    public function getModifiedImages(string $path): array
    {
        $images = [];
        $originalPathInfo = pathinfo($path);
        $originPath = ltrim($originalPathInfo['dirname'] . '/' . $originalPathInfo['basename'], '/');
        $files = $this->getFilesRecursive($originalPathInfo['dirname']);

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
        $files = $this->getFilesRecursive($originalPathInfo['dirname']);

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
