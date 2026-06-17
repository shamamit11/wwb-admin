<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class TagClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function index(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/tags')
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $tagId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/tags/{$tagId}")
        );
    }

    public function store(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/tags', $payload)
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $tagId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put("/admin/tags/{$tagId}", $payload)
        );
    }

    public function delete(string $token, ?string $tokenType = 'Bearer', int $tagId = 0): void
    {
        $this->api->handle(
            $this->api->authenticated($token, $tokenType)->delete("/admin/tags/{$tagId}")
        );
    }
}
