<?php

namespace App\Http\Controllers\Admin\Posts;

use App\Http\Controllers\Controller;
use App\Services\WideWebBlogApi\Clients\MediaClient;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthenticationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiAuthorizationException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiException;
use App\Services\WideWebBlogApi\Exceptions\WideWebBlogApiValidationException;
use App\Support\Auth\AdminSessionManager;
use App\Support\Media\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class InlineMediaUploadController extends Controller
{
    public function __invoke(
        Request $request,
        MediaClient $media,
        AdminSessionManager $session,
        MediaUrl $mediaUrl,
    ): JsonResponse {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,svg'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
        ]);

        try {
            $response = $media->store(
                $session->token() ?? '',
                $validated['file'],
                [
                    'alt_text' => filled($validated['alt_text'] ?? null) ? trim((string) $validated['alt_text']) : null,
                    'caption' => filled($validated['caption'] ?? null) ? trim((string) $validated['caption']) : null,
                    'source_type' => 'uploaded',
                ],
                $session->tokenType(),
            );

            $item = Arr::get($response, 'data', []);

            return response()->json([
                'data' => [
                    'id' => Arr::get($item, 'id'),
                    'original_filename' => (string) Arr::get($item, 'original_filename', 'Uploaded media'),
                    'alt_text' => Arr::get($item, 'alt_text'),
                    'caption' => Arr::get($item, 'caption'),
                    'mime_type' => (string) Arr::get($item, 'mime_type', ''),
                    'url' => $mediaUrl->resolve(Arr::get($item, 'url')),
                    'is_image' => str_starts_with((string) Arr::get($item, 'mime_type', ''), 'image/'),
                ],
            ], 201);
        } catch (WideWebBlogApiValidationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        } catch (WideWebBlogApiAuthenticationException $exception) {
            return response()->json([
                'message' => $exception->getMessage() ?: 'Your admin session is no longer valid for inline media uploads.',
            ], 401);
        } catch (WideWebBlogApiAuthorizationException $exception) {
            return response()->json([
                'message' => $exception->getMessage() ?: 'You do not have permission to upload inline media.',
            ], 403);
        } catch (WideWebBlogApiException $exception) {
            return response()->json([
                'message' => $exception->getMessage() ?: 'Inline media upload failed.',
            ], $exception->status() >= 400 ? $exception->status() : 422);
        }
    }
}
