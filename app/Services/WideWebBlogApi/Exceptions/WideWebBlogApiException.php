<?php

namespace App\Services\WideWebBlogApi\Exceptions;

use RuntimeException;

class WideWebBlogApiException extends RuntimeException
{
    public function __construct(
        string $message = 'Service API request failed.',
        protected int $status = 0,
        protected array $errors = [],
        protected array $meta = [],
    ) {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function meta(): array
    {
        return $this->meta;
    }
}
