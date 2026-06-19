<?php

namespace Tests\Feature\Posts;

use App\Livewire\Admin\Posts\Index;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PostIndexTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_posts_index_renders_service_backed_posts(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/posts*' => Http::response([
                'data' => [
                    $this->postResource([
                        'id' => 1,
                        'title' => 'Agent Systems Playbook',
                        'slug' => 'agent-systems-playbook',
                        'status' => 'draft',
                        'is_featured' => true,
                    ]),
                    $this->postResource([
                        'id' => 2,
                        'title' => 'Search Workflow Review',
                        'slug' => 'search-workflow-review',
                        'status' => 'published',
                        'published_at' => now()->subDay()->toISOString(),
                    ]),
                    $this->postResource([
                        'id' => 3,
                        'title' => 'Scheduled Launch Plan',
                        'slug' => 'scheduled-launch-plan',
                        'status' => 'scheduled',
                        'scheduled_for' => now()->addDays(3)->toISOString(),
                    ]),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('posts.index'));

        $response
            ->assertOk()
            ->assertSee('Create Post')
            ->assertSee('Agent Systems Playbook')
            ->assertSee('Search Workflow Review')
            ->assertSee('Scheduled Launch Plan')
            ->assertSee('Total Published')
            ->assertSee('1')
            ->assertSee('Scheduled Next 7 Days')
            ->assertSee('Featured Posts')
            ->assertSee('Posts');
    }

    public function test_draft_review_index_filters_to_ai_generated_drafts(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/posts')) {
                parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

                $this->assertSame('draft', $query['status'] ?? null);
                $this->assertSame('1', $query['is_ai_generated'] ?? null);

                return Http::response([
                    'data' => [
                        $this->postResource([
                            'id' => 41,
                            'title' => 'AI Editorial Review Checklist',
                            'is_ai_generated' => true,
                            'source_content_brief_id' => 14,
                            'source_content_topic_id' => 8,
                            'generated_by_ai_job_id' => 22,
                            'generated_by' => 'BlogWriterAgent',
                            'meta' => [
                                'source_content_brief_id' => 14,
                                'source_content_topic_id' => 8,
                                'ai_job_id' => 22,
                                'generated_by' => 'BlogWriterAgent',
                            ],
                        ]),
                    ],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('draft-review.index'));

        $response
            ->assertOk()
            ->assertSee('Draft Review')
            ->assertSee('AI Editorial Review Checklist')
            ->assertSee('Brief #14')
            ->assertSee('Topic #8')
            ->assertSee('Job #22')
            ->assertSee('Review');
    }

    public function test_post_schedule_action_maps_api_validation_errors(): void
    {
        session($this->authenticatedSession());

        $post = $this->postResource([
            'id' => 1,
            'title' => 'Agent Systems Playbook',
            'status' => 'draft',
        ]);

        Http::fake(function (Request $request) use ($post) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/posts')) {
                return Http::response(['data' => [$post]], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/1/schedule') {
                return Http::response([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'scheduled_for' => ['The scheduled for field must be a date after now.'],
                    ],
                ], 422);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openActionDialog', 'schedule', 1)
            ->set('scheduleFor', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('executeAction')
            ->assertHasErrors(['scheduleFor'])
            ->assertSee('The given data was invalid.')
            ->assertSee('The scheduled for field must be a date after now.');
    }

    public function test_posts_can_be_published_scheduled_unpublished_and_deleted_from_the_screen(): void
    {
        session($this->authenticatedSession());

        $draft = $this->postResource([
            'id' => 1,
            'title' => 'Agent Systems Playbook',
            'status' => 'draft',
            'published_at' => null,
            'scheduled_for' => null,
        ]);

        $published = $this->postResource([
            'id' => 2,
            'title' => 'Search Workflow Review',
            'status' => 'published',
            'published_at' => '2026-06-16T09:00:00Z',
        ]);

        $unpublished = $this->postResource([
            'id' => 3,
            'title' => 'Feature Launch Checklist',
            'status' => 'unpublished',
            'published_at' => null,
            'scheduled_for' => null,
        ]);

        $publishedDraft = array_replace($draft, [
            'status' => 'published',
            'published_at' => '2026-06-17T10:00:00Z',
        ]);

        $scheduledPost = array_replace($unpublished, [
            'status' => 'scheduled',
            'scheduled_for' => '2026-06-18T10:00:00Z',
        ]);

        $unpublishedLivePost = array_replace($published, [
            'status' => 'unpublished',
            'published_at' => null,
        ]);

        $getIndexCount = 0;

        Http::fake(function (Request $request) use (&$getIndexCount, $draft, $published, $unpublished, $publishedDraft, $scheduledPost, $unpublishedLivePost) {
            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/posts')) {
                $getIndexCount++;

                return Http::response([
                    'data' => match ($getIndexCount) {
                        1 => [$draft, $published, $unpublished],
                        2 => [$publishedDraft, $published, $unpublished],
                        3 => [$publishedDraft, $published, $scheduledPost],
                        4 => [$publishedDraft, $unpublishedLivePost, $scheduledPost],
                        default => [$publishedDraft, $unpublishedLivePost],
                    },
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/1/publish') {
                return Http::response(['data' => $publishedDraft], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/3/schedule') {
                $this->assertArrayHasKey('scheduled_for', $request->data());

                return Http::response(['data' => $scheduledPost], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/posts/2/unpublish') {
                return Http::response(['data' => $unpublishedLivePost], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === $this->apiBaseUrl.'/admin/posts/3') {
                return Http::response([], 204);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->assertSee('Agent Systems Playbook')
            ->assertSee('Feature Launch Checklist')
            ->call('openActionDialog', 'publish', 1)
            ->call('executeAction')
            ->assertHasNoErrors()
            ->call('openActionDialog', 'schedule', 3)
            ->set('scheduleFor', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('executeAction')
            ->assertHasNoErrors()
            ->call('openActionDialog', 'unpublish', 2)
            ->call('executeAction')
            ->assertHasNoErrors()
            ->call('openActionDialog', 'delete', 3)
            ->call('executeAction')
            ->assertDontSee('Feature Launch Checklist');
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

    protected function postResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01J00000000000000000000000',
            'title' => 'Post title',
            'slug' => 'post-title',
            'excerpt' => 'Short post summary.',
            'status' => 'draft',
            'visibility' => 'public',
            'published_at' => null,
            'scheduled_for' => null,
            'canonical_url' => null,
            'content_version' => 1,
            'reading_time_minutes' => 6,
            'word_count' => 1100,
            'is_featured' => false,
            'is_ai_generated' => false,
            'source_content_brief_id' => null,
            'source_content_topic_id' => null,
            'generated_by_ai_job_id' => null,
            'generated_by' => null,
            'meta' => [],
            'author' => [
                'id' => 9,
                'name' => 'Editorial Lead',
                'email' => 'editor@example.com',
            ],
            'category' => [
                'id' => 4,
                'name' => 'AI Systems',
                'slug' => 'ai-systems',
            ],
            'template' => null,
            'featured_media' => null,
            'tags' => [],
            'blocks' => [],
            'created_at' => '2026-06-10T09:00:00Z',
            'updated_at' => '2026-06-12T09:00:00Z',
        ], $overrides);
    }
}
