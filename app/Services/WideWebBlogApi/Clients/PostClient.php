<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class PostClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function index(string $token, ?string $tokenType = 'Bearer', array $filters = []): array
    {
        $query = array_filter(
            $filters,
            fn (mixed $value): bool => $value !== null && $value !== '',
        );

        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/posts', $query)
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $postId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/posts/{$postId}")
        );
    }

    public function store(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/posts', $payload)
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $postId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put("/admin/posts/{$postId}", $payload)
        );
    }

    public function delete(string $token, ?string $tokenType = 'Bearer', int $postId = 0): void
    {
        $this->api->handle(
            $this->api->authenticated($token, $tokenType)->delete("/admin/posts/{$postId}")
        );
    }

    public function publish(string $token, ?string $tokenType = 'Bearer', int $postId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/posts/{$postId}/publish")
        );
    }

    public function schedule(string $token, ?string $tokenType = 'Bearer', int $postId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/posts/{$postId}/schedule", $payload)
        );
    }

    public function unpublish(string $token, ?string $tokenType = 'Bearer', int $postId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/posts/{$postId}/unpublish")
        );
    }

    public function rewrite(string $token, ?string $tokenType = 'Bearer', int $postId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/posts/{$postId}/rewrite", $payload)
        );
    }

    public function suggestMetadata(string $token, ?string $tokenType = 'Bearer', int $postId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/posts/{$postId}/suggest-metadata", $payload)
        );
    }

    public function refineTitleExcerpt(string $token, ?string $tokenType = 'Bearer', int $postId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/posts/{$postId}/refine-title-excerpt", $payload)
        );
    }
}
