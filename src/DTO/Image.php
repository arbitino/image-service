<?php

declare(strict_types=1);

namespace Arbitino\ImageService\DTO;

final readonly class Image
{
    public string $path;
    public string $url;
    public string $mime;
    public string $dirname;
    public string $basename;
    public string $filename;
    public string $extension;
    public int $width;

    public int $height;

    public bool $valid;

    /** @param non-empty-array<string, string|int> $params */
    public function __construct(array $params)
    {
        $this->path = $params['path'] ?? '';
        $this->url = $params['url'] ?? '';
        $this->mime = $params['mime'] ?? '';
        $this->dirname = $params['dirname'] ?? '';
        $this->basename = $params['basename'] ?? '';
        $this->extension = $params['extension'] ?? '';
        $this->filename = $params['filename'] ?? '';
        $this->width = $params['width'] ?? 0;
        $this->height = $params['height'] ?? 0;

        $this->valid = isset($params['path']) && $params['path'];
    }
}
