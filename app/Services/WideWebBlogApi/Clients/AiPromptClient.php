<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class AiPromptClient
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
            $this->api->authenticated($token, $tokenType)->get('/admin/ai-prompts', $query)
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $promptId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/ai-prompts/{$promptId}")
        );
    }

    public function store(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/ai-prompts', $payload)
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $promptId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->patch("/admin/ai-prompts/{$promptId}", $payload)
        );
    }

    public function storeVersion(string $token, ?string $tokenType = 'Bearer', int $promptId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/ai-prompts/{$promptId}/versions", $payload)
        );
    }

    public function activateVersion(string $token, ?string $tokenType = 'Bearer', int $promptId = 0, int $versionId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/ai-prompts/{$promptId}/activate-version/{$versionId}")
        );
    }
}
