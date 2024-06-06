<?php

declare(strict_types=1);

namespace Arbitino\ImageService\Exceptions;

use RuntimeException;

class ImageConfigValidateException extends RuntimeException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
