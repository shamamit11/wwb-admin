<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class AiJobClient
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
            $this->api->authenticated($token, $tokenType)->get('/admin/ai-jobs', $query)
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $jobId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/ai-jobs/{$jobId}")
        );
    }

    public function topicDiscovery(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/ai-jobs/topic-discovery', $payload)
        );
    }

    public function retry(string $token, ?string $tokenType = 'Bearer', int $jobId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post("/admin/ai-jobs/{$jobId}/retry")
        );
    }
}
