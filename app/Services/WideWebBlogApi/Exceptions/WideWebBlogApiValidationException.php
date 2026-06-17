<?php

namespace App\Services\WideWebBlogApi\Exceptions;

class WideWebBlogApiValidationException extends WideWebBlogApiException
{
    public function __construct(
        string $message = 'Validation failed.',
        array $errors = [],
    ) {
        parent::__construct($message, 422, $errors);
    }
}
