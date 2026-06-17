<?php

namespace Tests\Feature\Media;

use App\Livewire\Admin\Media\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class MediaIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_media_index_renders_service_data_with_api_backed_filters(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/media')) {
                $this->assertStringContainsString('search=architecture', $request->url());
                $this->assertStringContainsString('source_type=uploaded', $request->url());
                $this->assertStringContainsString('used=1', $request->url());
                $this->assertStringContainsString('is_image=1', $request->url());

                return Http::response([
                    'data' => [
                        $this->mediaResource([
                            'id' => 1,
                            'original_filename' => 'architecture.webp',
                            'usage_count' => 2,
                        ]),
                    ],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('media.index', [
                'q' => 'architecture',
                'source' => 'uploaded',
                'used' => 'used',
                'image' => 'images',
            ]));

        $response
            ->assertOk()
            ->assertSee('architecture.webp')
            ->assertSee('Media Library')
            ->assertSee('Copy URL');
    }

    public function test_single_media_upload_uses_the_screen_flow_and_refreshes_the_list(): void
    {
        session($this->authenticatedSession());

        $uploaded = $this->mediaResource([
            'id' => 2,
            'original_filename' => 'diagram.webp',
            'alt_text' => 'Architecture diagram',
            'caption' => 'Agent memory map',
        ]);

        $getIndexCount = 0;

        Http::fake(function (Request $request) use (&$getIndexCount, $uploaded) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/media') {
                $getIndexCount++;

                return Http::response([
                    'data' => $getIndexCount === 1 ? [] : [$uploaded],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/media') {
                return Http::response(['data' => $uploaded], 201);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->set('singleFile', UploadedFile::fake()->image('diagram.webp', 1600, 900))
            ->set('uploadAltText', 'Architecture diagram')
            ->set('uploadCaption', 'Agent memory map')
            ->set('uploadSourceType', 'uploaded')
            ->call('uploadSingle')
            ->assertHasNoErrors()
            ->assertSee('diagram.webp');
    }

    public function test_batch_media_upload_uses_the_screen_flow_and_refreshes_the_list(): void
    {
        session($this->authenticatedSession());

        $first = $this->mediaResource([
            'id' => 3,
            'original_filename' => 'first.png',
        ]);

        $second = $this->mediaResource([
            'id' => 4,
            'original_filename' => 'second.png',
        ]);

        $getIndexCount = 0;

        Http::fake(function (Request $request) use (&$getIndexCount, $first, $second) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/media') {
                $getIndexCount++;

                return Http::response([
                    'data' => $getIndexCount === 1 ? [] : [$first, $second],
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/media/batch') {
                return Http::response(['data' => [$first, $second]], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('setUploadMode', 'batch')
            ->set('batchFiles', [
                UploadedFile::fake()->image('first.png', 800, 600),
                UploadedFile::fake()->image('second.png', 640, 480),
            ])
            ->set('uploadSourceType', 'uploaded')
            ->call('uploadBatch')
            ->assertHasNoErrors()
            ->assertSee('first.png')
            ->assertSee('second.png');
    }

    public function test_media_metadata_can_be_edited_from_the_detail_drawer(): void
    {
        session($this->authenticatedSession());

        $initial = $this->mediaResource([
            'id' => 1,
            'original_filename' => 'architecture.webp',
            'alt_text' => null,
            'caption' => null,
            'source_type' => 'uploaded',
            'usage_count' => 0,
            'usage' => [],
        ]);

        $updated = $this->mediaResource([
            'id' => 1,
            'original_filename' => 'architecture.webp',
            'alt_text' => 'Updated alt text',
            'caption' => 'Updated caption',
            'source_type' => 'stock',
            'source_url' => 'https://example.com/original',
            'attribution_text' => 'Example Provider',
            'usage_count' => 0,
            'usage' => [],
        ]);

        $getIndexCount = 0;

        Http::fake(function (Request $request) use (&$getIndexCount, $initial, $updated) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/media') {
                $getIndexCount++;

                return Http::response([
                    'data' => [$getIndexCount === 1 ? $initial : $updated],
                ], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/media/1') {
                return Http::response(['data' => $initial], 200);
            }

            if ($request->method() === 'PUT' && $request->url() === $this->apiBaseUrl.'/admin/media/1') {
                return Http::response(['data' => $updated], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->assertSee('architecture.webp')
            ->call('openDetailDrawer', 1)
            ->set('altText', 'Updated alt text')
            ->set('caption', 'Updated caption')
            ->set('sourceType', 'stock')
            ->set('sourceUrl', 'https://example.com/original')
            ->set('attributionText', 'Example Provider')
            ->call('saveMetadata')
            ->assertHasNoErrors()
            ->assertSee('Updated alt text')
            ->assertSee('Updated caption')
            ->assertSee('Example Provider');
    }

    public function test_unused_media_can_be_confirmed_and_deleted(): void
    {
        session($this->authenticatedSession());

        $initial = $this->mediaResource([
            'id' => 1,
            'original_filename' => 'unused.webp',
            'usage_count' => 0,
            'usage' => [],
        ]);

        Http::fake(function (Request $request) use ($initial) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/media') {
                static $count = 0;
                $count++;

                return Http::response([
                    'data' => $count === 1 ? [$initial] : [],
                ], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/media/1') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->assertSee('unused.webp')
            ->call('confirmDelete', 1)
            ->assertSet('deleteDialogOpen', true)
            ->assertSet('deleteBlocked', false)
            ->call('delete')
            ->assertDontSee('unused.webp');
    }

    public function test_in_use_media_is_blocked_from_delete_and_surfaces_usage_context(): void
    {
        session($this->authenticatedSession());

        Http::fake([
            $this->apiBaseUrl.'/admin/media' => Http::response([
                'data' => [
                    $this->mediaResource([
                        'id' => 1,
                        'original_filename' => 'featured.webp',
                        'usage_count' => 1,
                        'usage' => [
                            ['type' => 'featured_post', 'label' => 'Featured post usage', 'count' => 1],
                        ],
                    ]),
                ],
            ], 200),
        ]);

        Livewire::test(Index::class)
            ->assertSee('featured.webp')
            ->call('confirmDelete', 1)
            ->assertSet('deleteDialogOpen', true)
            ->assertSet('deleteBlocked', true)
            ->assertSee('Featured post usage')
            ->call('delete')
            ->assertSee('This asset is still in use and cannot be deleted.');
    }

    public function test_single_upload_maps_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/media') {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/media') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'file' => ['The file field is required.'],
                        'source_type' => ['The selected source type is invalid.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openUploadDrawer')
            ->set('singleFile', UploadedFile::fake()->image('invalid.webp', 100, 100))
            ->set('uploadSourceType', 'uploaded')
            ->call('uploadSingle')
            ->assertSee('The given data was invalid.');
    }

    protected function authenticatedSession(): array
    {
        return [
            config('widewebblog.session.token_key') => 'test-token',
            config('widewebblog.session.token_type_key') => 'Bearer',
            config('widewebblog.session.user_key') => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ],
        ];
    }

    protected function mediaResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'original_filename' => 'media.webp',
            'source_type' => 'uploaded',
            'source_url' => null,
            'attribution_text' => null,
            'mime_type' => 'image/webp',
            'file_size_bytes' => 123456,
            'width' => 1600,
            'height' => 900,
            'alt_text' => 'Media alt',
            'caption' => 'Media caption',
            'url' => 'https://cdn.example.com/media.webp',
            'status' => 'ready',
            'usage_count' => 0,
            'usage' => [],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
