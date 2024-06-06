<?php

declare(strict_types=1);

namespace Arbitino\ImageService\Storage;

interface Storage
{
    public function makeDirectory(string $dir): bool;

    public function exists(string $path): bool;
    public function delete(string $path): bool;

    /**
     * @param string $dir
     * @return string[]
     */
    public function getFiles(string $dir): array;

    /**
     * @param string $dir
     * @return string[]
     */
    public function getFilesRecursive(string $dir): array;

    public function url(string $path): string;

    public function path(string $path): string;

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
    public function imageParams(string $path): array;

    /**
     * @return string[]
     */
    public function getModifiedImages(string $path): array;

    public function haveModifiedImages(string $path): bool;

    public function deleteModifiedImages(string $path): void;
}
