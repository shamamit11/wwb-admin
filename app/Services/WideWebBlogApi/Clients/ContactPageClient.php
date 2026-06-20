<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class ContactPageClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function show(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/contact-page')
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put('/admin/contact-page', $payload)
        );
    }
}
