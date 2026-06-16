<?php

namespace App\Services\WideWebBlogApi;

use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class WideWebBlogApi
{
    public function request(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->baseUrl(config('widewebblog.api.base_url'))
            ->timeout(config('widewebblog.api.timeout'))
            ->connectTimeout(config('widewebblog.api.connect_timeout'))
            ->retry(
                config('widewebblog.api.retry_times'),
                config('widewebblog.api.retry_sleep_ms'),
                throw: false,
            );
    }

    public function authenticated(string $token, ?string $tokenType = 'Bearer'): PendingRequest
    {
        $request = $this->request();

        if ($tokenType && strtolower($tokenType) !== 'bearer') {
            return $request->withHeaders([
                'Authorization' => sprintf('%s %s', $tokenType, $token),
            ]);
        }

        return $request->withToken($token);
    }

    public function handle(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $message = $response->json('message') ?: 'Service API request failed.';

        match ($response->status()) {
            401 => throw new WideWebBlogApiAuthenticationException($message),
            403 => throw new WideWebBlogApiAuthorizationException($message),
            422 => throw new WideWebBlogApiValidationException($message, $response->json('errors', [])),
            default => throw new WideWebBlogApiException($message),
        };
    }
}
