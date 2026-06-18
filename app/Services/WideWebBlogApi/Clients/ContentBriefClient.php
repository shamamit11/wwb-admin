<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class ContentBriefClient
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
            $this->api->authenticated($token, $tokenType)->get('/admin/content-briefs', $query)
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $briefId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/content-briefs/{$briefId}")
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $briefId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->patch("/admin/content-briefs/{$briefId}", $payload)
        );
    }

    public function approve(string $token, ?string $tokenType = 'Bearer', int $briefId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/content-briefs/{$briefId}/approve")
        );
    }

    public function generateDraft(string $token, ?string $tokenType = 'Bearer', int $briefId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/content-briefs/{$briefId}/generate-draft", $payload)
        );
    }
}
