<?php

namespace App\Services\WideWebBlogApi\Exceptions;

class WideWebBlogApiValidationException extends WideWebBlogApiException
{
    public function __construct(
        string $message = 'Validation failed.',
        protected array $errors = [],
    ) {
        parent::__construct($message);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
