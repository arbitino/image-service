<?php

declare(strict_types=1);

namespace Arbitino\ImageService\Config;

use Arbitino\ImageService\DTO\Image;
use Arbitino\ImageService\DTO\ServiceConfig;
use Arbitino\ImageService\Exceptions\ImageConfigValidateException;

final class ValidatedImageConfig implements ImageConfig
{
    private Image $image;

    private ServiceConfig $config;

    private int $quality;
    private bool $convert;
    private bool $resize;

    /** @var null|int[] $size */
    private ?array $size;
    private string $method;
    private string $format;
    private int $defaultQuality = 95;
    private string $defaultMethod = 'resize';


    /**
     * @param ServiceConfig $config
     * @param Image $image
     * @param array{quality: ?int, method: ?string, format: ?string, resize: ?string} $params
     * @throws ImageConfigValidateException
     */
    public function __construct(
        ServiceConfig $config,
        Image $image,
        array $params,
    ) {
        $this->config = $config;
        $this->image = $image;

        $quality = $params['quality'] ?? null;
        $method = $params['method'] ?? null;

        if (!$quality) {
            $quality = $this->config->defaultQuality ?? $this->defaultQuality;
        }

        if (!$method) {
            $method = $this->defaultMethod;
        }

        $this->setFormat($params['format'] ?? $image->extension);
        $this->setSize($this->getResizeValues($params['resize'] ?? null));
        $this->setQuality($quality);
        $this->setMethod($method);
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function isConvert(): bool
    {
        return $this->convert;
    }

    public function isResize(): bool
    {
        return $this->resize;
    }

    /**
     * @return int[]|null
     */
    public function getSize(): ?array
    {
        return $this->size;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getImage(): Image
    {
        return $this->image;
    }

    public function getPath(): string
    {
        return $this->image->path;
    }

    public function setQuality(int $quality): void
    {
        $this->quality = $quality;
        $this->validateQuality();
    }

    /**
     * @param string|null|int[] $size
     * @throws ImageConfigValidateException
     */
    public function setSize(null|string|array $size): void
    {
        $this->size = $this->getResizeValues($size);
        $this->validateSize();
    }

    /**
     * @throws ImageConfigValidateException
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;

        $this->validateMethod();
    }

    /**
     * @throws ImageConfigValidateException
     */
    public function setFormat(string $format): void
    {
        $this->format = strtolower($format);
        $this->validateFormat();
        $this->setConvert();
    }

    public function getDefaultQuality(): int
    {
        return $this->defaultQuality;
    }

    public function getDefaultMethod(): string
    {
        return $this->defaultMethod;
    }

    /**
     * @throws ImageConfigValidateException
     */
    private function validateFormat(): void
    {
        if ($this->format && !in_array($this->format, $this->config->allowFormats)) {
            throw new ImageConfigValidateException('The image format is not allowed');
        }
    }

    private function setConvert(): void
    {
        if (strtolower($this->image->extension ?: '') === strtolower($this->format ?: '')) {
            $this->convert = false;
            return;
        }

        if (
            $this->image->extension === 'jpg'
            && $this->format === 'jpeg'
        ) {
            $this->convert = false;
            return;
        }

        if (
            $this->image->extension === 'jpeg'
            && $this->format === 'jpg'
        ) {
            $this->convert = false;
            return;
        }

        $this->convert = true;
    }

    /**
     * @throws ImageConfigValidateException
     */
    private function validateSize(): void
    {
        $this->resize = is_array($this->size)
            && ($this->image->width !== $this->size[0] || $this->image->height !== $this->size[1]);

        if ($this->config->allowSizes === '*') {
            return;
        }

        if (!in_array($this->sizeToString($this->size), $this->config->allowSizes)) {
            throw new ImageConfigValidateException('size is not allowed');
        }
    }

    private function validateQuality(): void
    {
        if ($this->quality < 1) {
            $this->quality = 1;
            return;
        }

        if ($this->quality > 100) {
            $this->quality = 100;
        }
    }

    /**
     * @throws ImageConfigValidateException
     */
    private function validateMethod(): void
    {
        if (!in_array($this->method, $this->config->allowMethods)) {
            throw new ImageConfigValidateException('The method for editing the image is not allowed');
        }
    }

    /**
     * @param int[]|string|null $sizes
     * @return int[]|null
     */
    private function getResizeValues(null|array|string $sizes): ?array
    {
        if (!$sizes) {
            return null;
        }

        if (is_string($sizes)) {
            return array_map(function ($item) {
                return (int) $item;
            }, explode('x', $sizes));
        }

        return [
            (int) $sizes[0],
            (int) $sizes[1],
        ];
    }

    /**
     * @param int[]|null $size
     * @return string
     */
    private function sizeToString(?array $size): string
    {
        if (!$size) {
            return '*';
        }

        return $size[0] . 'x' . $size[1];
    }
}
