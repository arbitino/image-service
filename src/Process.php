<?php

declare(strict_types=1);

namespace Arbitino\ImageService;

use Arbitino\ImageService\Config\ImageConfig;
use Arbitino\ImageService\DTO\Image;
use Arbitino\ImageService\DTO\ProcessResult;
use Arbitino\ImageService\Exceptions\ImageConfigValidateException;
use Arbitino\ImageService\Manager\Manager;
use Arbitino\ImageService\Storage\Storage;

final class Process
{
    protected string $newDir;
    protected string $newPath;

    public function __construct(
        protected readonly Manager     $manager,
        protected readonly Storage     $storage,
        protected readonly ImageConfig $config,

        /** @var array<array-key, mixed> */
        protected readonly array       $imageManagerParams,
    ) {}

    public function setQuality(int $quality): self
    {
        $this->config->setQuality($quality);

        return $this;
    }

    /**
     * @throws ImageConfigValidateException
     */
    public function setFormat(string $format): self
    {
        $this->config->setFormat($format);

        return $this;
    }

    /**
     * @param string|int[]|null $sizes
     * @return $this
     * @throws ImageConfigValidateException
     */
    public function setSizes(null|string|array $sizes): self
    {
        $this->config->setSize($sizes);

        return $this;
    }

    /**
     * @throws ImageConfigValidateException
     */
    public function setMethod(string $method): self
    {
        $this->config->setMethod($method);

        return $this;
    }

    /**
     * @return array{method: string, format: string, quality: int, size: int[]|null}
     */
    public function getParams(): array
    {
        return [
            'method' => $this->config->getMethod(),
            'format' => $this->config->getFormat(),
            'quality' => $this->config->getQuality(),
            'size' => $this->config->getSize(),
        ];
    }

    public function process(): ProcessResult
    {
        $this->setNewPaths();
        $this->makeDir();
        $this->makeImage();

        return $this->results();
    }

    protected function makeDir(): void
    {
        if (!$this->storage->exists($this->newDir)) {
            $this->storage->makeDirectory($this->newDir);
        }
    }

    protected function makeImage(): void
    {
        if ($this->storage->exists($this->newPath)) {
            return;
        }

        $this->manager->makeImage($this->config->getPath());
        $this->manager->setQuality($this->config->getQuality());

        if ($this->config->isResize()) {
            $size = $this->config->getSize();

            $this->manager->resizeImage(
                $this->config->getMethod(),
                $size[0],
                $size[1],
                $this->imageManagerParams['resize'] ?? [],
            );
        }

        if ($this->config->isConvert()) {
            $this->manager->convertImage(
                $this->config->getFormat(),
                $this->config->getQuality(),
                $this->imageManagerParams['convert'] ?? [],
            );
        }

        $this->manager->saveImage(
            $this->storage->path($this->newPath),
        );
    }

    protected function results(): ProcessResult
    {
        $newImage = new Image($this->storage->imageParams($this->newPath));

        return new ProcessResult($newImage, $this->config->getImage());
    }

    protected function setNewPaths(): void
    {
        $this->setNewDir();
        $this->setNewPath();
    }

    protected function setNewDir(): void
    {
        $newDir = sprintf(
            '%s/modify/%s/%s/',
            $this->config->getImage()->dirname,
            $this->config->getQuality(),
            $this->config->getMethod(),
        );

        if ($this->config->isResize()) {
            $size = $this->config->getSize();
            $newDir .= $size[0] . 'x' . $size[1] . '/';
        }

        if ($this->config->isConvert()) {
            $newDir .= $this->config->getFormat() . '/';
        }

        $this->newDir = $newDir;
    }

    protected function setNewPath(): void
    {
        $image = $this->config->getImage();

        $this->newPath = $this->config->isConvert()
            ? $this->newDir . $image->filename . '.' . $this->config->getFormat()
            : $this->newDir . $image->basename;
    }
}
