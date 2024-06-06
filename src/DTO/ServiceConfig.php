<?php

declare(strict_types=1);

namespace Arbitino\ImageService\DTO;

final readonly class ServiceConfig
{
    public function __construct(
        /** @var string[] */
        public array $allowFormats = ['webp', 'png', 'jpg', 'jpeg', 'gif'],
        /** @var string[] */
        public array $allowMethods = ['resize', 'crop', 'fit'],
        /** @var string|int[] */
        public string|array $allowSizes  = '*',
        public int $defaultQuality  = 95,
    ) {}
}
