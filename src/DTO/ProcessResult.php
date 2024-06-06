<?php

declare(strict_types=1);

namespace Arbitino\ImageService\DTO;

final readonly class ProcessResult
{
    public function __construct(
        public Image $new,
        public Image $original,
    ) {}
}
