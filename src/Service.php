<?php

declare(strict_types=1);

namespace Arbitino\ImageService;

use Arbitino\ImageService\Config\ValidatedImageConfig;
use Arbitino\ImageService\DTO\Image;
use Arbitino\ImageService\DTO\ServiceConfig;
use Arbitino\ImageService\Exceptions\ImageConfigValidateException;
use Arbitino\ImageService\Manager\Manager;
use Arbitino\ImageService\Storage\Storage;

final readonly class Service
{
    public function __construct(
        public Manager       $manager,
        public Storage       $storage,
        public ServiceConfig $config,
    ) {}

    /**
     * @param string $path
     * @param string|array{quality: ?int, method: ?string, format: ?string, resize: ?string, extra: ?mixed} $params
     * @return Process
     * @throws ImageConfigValidateException
     */
    public function modify(string $path, string|array $params = ''): Process
    {
        $image = new Image($this->storage->imageParams($path));
        $params = is_array($params) ? $params : $this->setParamsByString($params);

        $config = new ValidatedImageConfig(
            $this->config,
            $image,
            $params,
        );

        return new Process(
            $this->manager,
            $this->storage,
            $config,
            $params['extra'] ?? [],
        );
    }

    /**
     * @param string $path
     * @return string[]
     */
    public function getModifiedImagesByOriginal(string $path): array
    {
        return $this->storage->getModifiedImages($path);
    }

    public function imageHaveModifiedImages(string $path): bool
    {
        return $this->storage->haveModifiedImages($path);
    }

    public function deleteModifiedImagesByOriginal(string $path, bool $deleteOriginal = true): void
    {
        $this->storage->deleteModifiedImages($path);

        if ($deleteOriginal) {
            $this->storage->delete($path);
        }
    }

    /**
     * @return array{resize: null|string, format: null|string, quality: int, method: null|string}
     */
    protected function setParamsByString(string $params): array
    {
        return [
            'resize' => $this->extractValue($params, 'r'),
            'format' => $this->extractValue($params, 'f'),
            'quality' => (int) $this->extractValue($params, 'q'),
            'method' => $this->extractValue($params, 'm'),
        ];
    }

    protected function extractValue(string $imageString, string $char): ?string
    {
        preg_match('/' . $char . '\/(.*?)\//', $imageString, $matches);
        return $matches[1] ?? null;
    }
}
