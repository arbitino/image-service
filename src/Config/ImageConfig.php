<?php

declare(strict_types=1);

namespace Arbitino\ImageService\Config;

use Arbitino\ImageService\DTO\Image;
use Arbitino\ImageService\Exceptions\ImageConfigValidateException;

interface ImageConfig
{
    public function getQuality(): int;

    public function isConvert(): bool;

    public function isResize(): bool;

    /**
     * @return int[]|null
     */
    public function getSize(): ?array;

    public function getMethod(): string;

    public function getFormat(): string;

    public function getImage(): Image;

    public function getPath(): string;

    public function setQuality(int $quality): void;

    /**
     * @param string|null|int[] $size
     * @throws ImageConfigValidateException
     */
    public function setSize(null|string|array $size): void;

    /**
     * @throws ImageConfigValidateException
     */
    public function setMethod(string $method): void;

    /**
     * @throws ImageConfigValidateException
     */
    public function setFormat(string $format): void;
}
