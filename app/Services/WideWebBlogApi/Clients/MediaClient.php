<?php

namespace App\Services\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\WideWebBlogApi;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;

class MediaClient
{
    public function __construct(
        protected WideWebBlogApi $api,
    ) {
    }

    public function index(string $token, ?string $tokenType = 'Bearer', array $filters = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get('/admin/media', array_filter(
                $filters,
                static fn (mixed $value): bool => $value !== null && $value !== ''
            ))
        );
    }

    public function show(string $token, ?string $tokenType = 'Bearer', int $mediaId = 0): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->get("/admin/media/{$mediaId}")
        );
    }

    public function store(string $token, UploadedFile $file, array $payload = [], ?string $tokenType = 'Bearer'): array
    {
        $request = $this->multipartAuthenticated($token, $tokenType)
            ->attach(
                'file',
                file_get_contents($file->getRealPath()) ?: '',
                $file->getClientOriginalName(),
                ['Content-Type' => $file->getMimeType() ?: 'application/octet-stream']
            );

        return $this->api->handle(
            $request->post('/admin/media', $this->multipartPayload($payload))
        );
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function batchStore(string $token, array $files, array $payload = [], ?string $tokenType = 'Bearer'): array
    {
        $request = $this->multipartAuthenticated($token, $tokenType);

        foreach ($files as $file) {
            $request = $request->attach(
                'files[]',
                file_get_contents($file->getRealPath()) ?: '',
                $file->getClientOriginalName(),
                ['Content-Type' => $file->getMimeType() ?: 'application/octet-stream']
            );
        }

        return $this->api->handle(
            $request->post('/admin/media/batch', $this->multipartPayload($payload))
        );
    }

    public function update(string $token, ?string $tokenType = 'Bearer', int $mediaId = 0, array $payload = []): array
    {
        return $this->api->handle(
            $this->api->authenticated($token, $tokenType)->put("/admin/media/{$mediaId}", $payload)
        );
    }

    public function delete(string $token, ?string $tokenType = 'Bearer', int $mediaId = 0): void
    {
        $this->api->handle(
            $this->api->authenticated($token, $tokenType)->delete("/admin/media/{$mediaId}")
        );
    }

    protected function multipartAuthenticated(string $token, ?string $tokenType = 'Bearer'): PendingRequest
    {
        $request = $this->api->multipartRequest();

        if ($tokenType && strtolower($tokenType) !== 'bearer') {
            return $request->withHeaders([
                'Authorization' => sprintf('%s %s', $tokenType, $token),
            ]);
        }

        return $request->withToken($token);
    }

    protected function multipartPayload(array $payload): array
    {
        return array_filter(
            $payload,
            static fn (mixed $value): bool => $value !== null && $value !== ''
        );
    }
}
