<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class AboutPageClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function show(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/about-page')
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put('/admin/about-page', $payload)
        );
    }
}
