<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class CategoryClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function index(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/categories')
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $categoryId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/categories/{$categoryId}")
        );
    }

    public function store(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/categories', $payload)
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $categoryId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put("/admin/categories/{$categoryId}", $payload)
        );
    }

    public function delete(string $token, ?string $tokenType = 'Bearer', int $categoryId = 0): void
    {
        $this->api->handle(
            $this->api->authenticated($token, $tokenType)->delete("/admin/categories/{$categoryId}")
        );
    }
}
