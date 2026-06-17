<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class MediaClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function index(string $token, ?string $tokenType = 'Bearer', array $filters = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/media', array_filter(
                $filters,
                static fn (mixed $value): bool => $value !== null && $value !== ''
            ))
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $mediaId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/media/{$mediaId}")
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $mediaId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put("/admin/media/{$mediaId}", $payload)
        );
    }

    public function delete(string $token, ?string $tokenType = 'Bearer', int $mediaId = 0): void
    {
        $this->api->handle(
            $this->api->authenticated($token, $tokenType)->delete("/admin/media/{$mediaId}")
        );
    }
}
