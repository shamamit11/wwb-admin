<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class ContentTopicClient
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
            $this->api->authenticated($token, $tokenType)->get('/admin/content-topics', $query)
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $topicId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/content-topics/{$topicId}")
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $topicId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->patch("/admin/content-topics/{$topicId}", $payload)
        );
    }

    public function approve(string $token, ?string $tokenType = 'Bearer', int $topicId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/content-topics/{$topicId}/approve", $payload)
        );
    }

    public function reject(string $token, ?string $tokenType = 'Bearer', int $topicId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/content-topics/{$topicId}/reject", $payload)
        );
    }

    public function markUsed(string $token, ?string $tokenType = 'Bearer', int $topicId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/content-topics/{$topicId}/mark-used", $payload)
        );
    }

    public function generateBrief(string $token, ?string $tokenType = 'Bearer', int $topicId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/content-topics/{$topicId}/generate-brief")
        );
    }
}
