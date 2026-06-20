<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class ContactSubmissionClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function index(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/contact-submissions')
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', string $submissionId): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/contact-submissions/{$submissionId}")
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', string $submissionId, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->patch("/admin/contact-submissions/{$submissionId}", $payload)
        );
    }
}
