<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class SeoClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function show(string $token, ?string $tokenType = 'Bearer', string $seoableType = 'post', int $seoableId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/seo/{$seoableType}/{$seoableId}")
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', string $seoableType = 'post', int $seoableId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put("/admin/seo/{$seoableType}/{$seoableId}", $payload)
        );
    }

    public function score(string $token, ?string $tokenType = 'Bearer', string $seoableType = 'post', int $seoableId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/seo/score/{$seoableType}/{$seoableId}")
        );
    }

    public function schema(string $token, ?string $tokenType = 'Bearer', string $seoableType = 'post', int $seoableId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/seo/schema/{$seoableType}/{$seoableId}")
        );
    }

    public function sitemap(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/seo/sitemap')
        );
    }

    public function rss(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/feeds/rss')
        );
    }
}
