<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class NewsItemClient
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
            $this->api->authenticated($token, $tokenType)->get('/admin/news-items', $query)
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $newsItemId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/news-items/{$newsItemId}")
        );
    }

    public function discover(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/news-items/discover', $payload)
        );
    }

    public function score(string $token, ?string $tokenType = 'Bearer', int $newsItemId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/news-items/{$newsItemId}/score")
        );
    }

    public function extract(string $token, ?string $tokenType = 'Bearer', int $newsItemId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/news-items/{$newsItemId}/extract")
        );
    }

    public function route(string $token, ?string $tokenType = 'Bearer', int $newsItemId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/news-items/{$newsItemId}/route")
        );
    }
}
