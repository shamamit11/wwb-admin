<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class KnowledgeBaseClient
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
            $this->api->authenticated($token, $tokenType)->get('/admin/knowledge-base', $query)
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $entryId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/knowledge-base/{$entryId}")
        );
    }

    public function store(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/knowledge-base', $payload)
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $entryId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put("/admin/knowledge-base/{$entryId}", $payload)
        );
    }

    public function delete(string $token, ?string $tokenType = 'Bearer', int $entryId = 0): void
    {
        $this->api->handle(
            $this->api->authenticated($token, $tokenType)->delete("/admin/knowledge-base/{$entryId}")
        );
    }
}
