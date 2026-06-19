<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;

class AuthClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $payload = array_filter([
            'email' => $email,
            'password' => $password,
            'device_name' => $deviceName,
        ], fn ($value) => $value !== null && $value !== '');

        return $this->api->handle(
            $this->api->request()->post('/auth/login', $payload)
        );
    }

    public function me(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/auth/me')
        );
    }

    public function adminMe(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/me')
        );
    }

    public function logout(string $token, ?string $tokenType = 'Bearer'): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/auth/logout')
        );
    }

    public function changePassword(string $token, ?string $tokenType = 'Bearer', array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->post('/admin/change-password', $payload)
        );
    }
}
