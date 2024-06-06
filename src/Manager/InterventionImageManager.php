<?php

declare(strict_types=1);

namespace Arbitino\ImageService\Manager;

use Arbitino\ImageService\Exceptions\ImageManagerException;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;

final class InterventionImageManager implements Manager
{
    private ImageInterface|EncodedImageInterface $image;
    private ImageManager $manager;

    private int $quality = 95;

    public function __construct(?DriverInterface $driver = null)
    {
        if ($driver === null) {
            $driver = new Driver();
        }

        $this->manager = new ImageManager($driver);
    }

    public function makeImage(string $path): void
    {
        $this->image = $this->manager->read($path);
    }

    /**
     * @param string $method
     * @param int $with
     * @param int $height
     * @param array<string, string|int|bool> $extra_params
     * @return void
     * @throws ImageManagerException
     */
    public function resizeImage(string $method, int $with, int $height, array $extra_params = []): void
    {
        match($method) {
            'crop' => $this->crop($with, $height, $extra_params),
            'fit' => $this->fit($with, $height, $extra_params),
            'resize' => $this->resize($with, $height, $extra_params),
            default => throw new ImageManagerException("Unknown method $method"),
        };
    }


    /**
     * @param string $format
     * @param int $quality
     * @param array<string, string|int|bool> $extra_params
     * @return void
     * @throws ImageManagerException
     */
    public function convertImage(string $format, int $quality = 95, array $extra_params = []): void
    {
        $this->quality = $quality;

        $this->image = match (strtolower($format)) {
            'gif', 'image/gif' => $this->image->toGif(),
            'png', 'image/png' => $this->image->toPng(),
            'jpg', 'jpeg', 'image/jpg', 'image/jpeg' => $this->image->toJpeg($this->quality),
            'webp', 'image/webp' => $this->image->toWebp($this->quality),
            default => throw new ImageManagerException('Format ' . $format . 'not supported'),
        };
    }

    /**
     * @throws ImageManagerException
     */
    public function setQuality(int $quality): void
    {
        if ($quality < 1) {
            throw new ImageManagerException('Quality must be more than 0');
        }

        if ($quality > 100) {
            throw new ImageManagerException('Quality must be less than 101');
        }

        $this->quality = $quality;
    }

    public function saveImage(string $path): void
    {
        $this->image->save($path, $this->quality);
    }

    /**
     * @param int $w
     * @param int $h
     * @param array<string, string|int|bool> $params
     * @return void
     */
    protected function crop(int $w, int $h, array $params): void
    {
        $x = isset($params['x']) ? (int) $params['x'] : 0;
        $y = isset($params['y']) ? (int) $params['y'] : 0;
        $position = $params['position'] ?? 'top-left';
        $background = $params['background'] ?? 'ffffff';

        $this->image->crop($w, $h, $x, $y, $background, $position);
    }

    /**
     * @param int $w
     * @param int $h
     * @param array<string, string|int|bool> $params
     * @return void
     */
    protected function fit(int $w, int $h, array $params): void
    {
        $allow_enlarge = $params['allow_enlarge'] ?? false;
        $position = $params['position'] ?? 'center';

        if ($allow_enlarge) {
            $this->image->cover($w, $h, $position);
            return;
        }

        $this->image->coverDown($w, $h, $position);
    }

    /**
     * @param int $w
     * @param int $h
     * @param array<string, string|int|bool> $params
     * @return void
     */
    protected function resize(int $w, int $h, array $params): void
    {
        $allow_enlarge = $params['allow_enlarge'] ?? false;

        if ($allow_enlarge) {
            $this->resizeEnlarge($w, $h);
            return;
        }

        $this->resizeProportionally($w, $h);
    }

    protected function resizeEnlarge(int $w, int $h): void
    {
        if ($h === 0) {
            $this->image->scale(width: $w);
        } elseif ($w === 0) {
            $this->image->scale(height: $h);
        } else {
            $this->image->resize($w, $h);
        }
    }

    protected function resizeProportionally(int $w, int $h): void
    {
        if ($h === 0) {
            $this->image->scaleDown(width: $w);
        } elseif ($w === 0) {
            $this->image->scaleDown(height: $h);
        } else {
            $this->image->resizeDown($w, $h);
        }
    }
}
