<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class TemplateClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function index(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/templates')
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $templateId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/templates/{$templateId}")
        );
    }

    public function store(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/templates', $payload)
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $templateId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put("/admin/templates/{$templateId}", $payload)
        );
    }

    public function delete(string $token, ?string $tokenType = 'Bearer', int $templateId = 0): void
    {
        $this->api->handle(
            $this->api->authenticated($token, $tokenType)->delete("/admin/templates/{$templateId}")
        );
    }

    public function preview(string $token, ?string $tokenType = 'Bearer', int $templateId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/templates/{$templateId}/preview", $payload)
        );
    }

    public function seedPost(string $token, ?string $tokenType = 'Bearer', int $templateId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/templates/{$templateId}/seed-post", $payload)
        );
    }
}
