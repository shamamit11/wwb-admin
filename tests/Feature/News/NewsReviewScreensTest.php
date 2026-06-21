<?php

namespace Tests\Feature\News;

use App\Livewire\Admin\News\Index;
use App\Livewire\Admin\News\Show;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class NewsReviewScreensTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_news_index_renders_review_filters_and_columns(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/categories' => Http::response([
                'data' => [
                    $this->categoryResource(['id' => 5, 'name' => 'AI Tools']),
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/news-items*' => Http::response([
                'data' => [
                    $this->newsResource([
                        'id' => 21,
                        'title' => 'Anthropic ships new coding workflow',
                        'publisher_name' => 'TechCrunch',
                        'status' => 'screened',
                        'latest_score' => ['total_score' => 82, 'decision' => 'topic'],
                        'latest_route' => ['route' => 'topic'],
                    ]),
                ],
            ], 200),
        ]);

        $response = $this->withSession($this->authenticatedSession())
            ->get(route('news.index'));

        $response
            ->assertOk()
            ->assertSee('Discover News')
            ->assertSee('All decisions')
            ->assertSee('Anthropic ships new coding workflow')
            ->assertSee('TechCrunch')
            ->assertSee('Review');
    }

    public function test_discover_news_uses_category_limit_and_sync_payload(): void
    {
        session($this->authenticatedSession());

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/categories') {
                return Http::response([
                    'data' => [
                        $this->categoryResource(['id' => 5, 'name' => 'AI Tools']),
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && str_starts_with($request->url(), $this->apiBaseUrl.'/admin/news-items')) {
                return Http::response(['data' => []], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/news-items/discover') {
                $this->assertSame(5, $request['category_id']);
                $this->assertSame(12, $request['limit']);
                $this->assertTrue($request['sync']);

                return Http::response([
                    'data' => [
                        $this->newsResource(['id' => 44, 'title' => 'Fresh sync intake']),
                    ],
                ], 201);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Index::class)
            ->call('openDiscoveryDialog')
            ->set('discoveryCategoryId', '5')
            ->set('discoveryLimit', '12')
            ->set('discoverySync', true)
            ->call('discoverNews')
            ->assertHasNoErrors()
            ->assertSet('discoveryDialogOpen', false);
    }

    public function test_news_detail_can_score_extract_and_route_article(): void
    {
        session($this->authenticatedSession());

        $discovered = $this->newsResource([
            'id' => 8,
            'status' => 'discovered',
            'latest_score' => null,
            'latest_extraction' => null,
            'latest_route' => null,
        ]);

        $screened = $this->newsResource([
            'id' => 8,
            'status' => 'screened',
            'latest_score' => [
                'id' => 91,
                'total_score' => 81,
                'decision' => 'topic',
                'reasoning' => 'Strong trend fit.',
                'relevance_score' => 12,
                'freshness_score' => 11,
                'credibility_score' => 10,
                'pillar_fit_score' => 13,
                'evergreen_potential_score' => 12,
                'novelty_score' => 11,
                'business_value_score' => 12,
                'scored_at' => now()->subMinute()->toISOString(),
            ],
        ]);

        $extracted = array_replace_recursive($screened, [
            'status' => 'extracted',
            'latest_extraction' => [
                'id' => 51,
                'excerpt' => 'A concise extraction excerpt.',
                'facts_json' => ['Fact one'],
                'entities_json' => ['OpenAI'],
                'claims_json' => ['Claim one'],
                'metadata' => ['source' => 'extractor'],
                'extracted_at' => now()->toISOString(),
            ],
        ]);

        $routed = array_replace_recursive($extracted, [
            'status' => 'routed',
            'latest_route' => [
                'id' => 61,
                'route' => 'topic',
                'knowledge_base_entry_id' => null,
                'content_topic_id' => 14,
                'post_id' => null,
                'metadata' => ['confidence' => 'high'],
                'routed_at' => now()->toISOString(),
                'knowledge_base_entry' => null,
                'content_topic' => [
                    'id' => 14,
                    'title' => 'AI coding tools',
                    'slug' => 'ai-coding-tools',
                    'status' => 'suggested',
                ],
                'post' => null,
            ],
        ]);

        Http::fake(function (Request $request) use ($discovered, $screened, $extracted, $routed) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/news-items/8') {
                return Http::response(['data' => $discovered], 200);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/news-items/8/score') {
                return Http::response(['data' => $screened], 202);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/news-items/8/extract') {
                return Http::response(['data' => $extracted], 202);
            }

            if ($request->method() === 'POST' && $request->url() === $this->apiBaseUrl.'/admin/news-items/8/route') {
                return Http::response(['data' => $routed], 202);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        Livewire::test(Show::class, ['news' => 8])
            ->assertSee('Score')
            ->call('score')
            ->assertSet('newsItem.status', 'screened')
            ->assertSet('newsItem.latest_score.decision', 'topic')
            ->call('extract')
            ->assertSet('newsItem.status', 'extracted')
            ->assertSet('newsItem.latest_extraction.excerpt', 'A concise extraction excerpt.')
            ->call('routeNews')
            ->assertSet('newsItem.status', 'routed')
            ->assertSet('newsItem.latest_route.route', 'topic');
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

    protected function categoryResource(array $overrides = []): array
    {
        return array_replace([
            'id' => 1,
            'ulid' => '01JTESTCATEGORY',
            'parent_id' => null,
            'name' => 'Category',
            'slug' => 'category',
            'description' => null,
            'is_active' => true,
            'canonical_url' => '/category',
            'sort_order' => 0,
            'created_at' => now()->subDay()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }

    protected function newsResource(array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => 1,
            'external_id' => 'ext-1',
            'provider' => 'newsapi',
            'status' => 'discovered',
            'title' => 'AI platform update',
            'normalized_title' => 'ai platform update',
            'url' => 'https://example.com/article',
            'canonical_url' => 'https://example.com/article',
            'description' => 'Article summary.',
            'author' => 'Reporter',
            'publisher_name' => 'Publisher',
            'language' => 'en',
            'country' => 'us',
            'metadata' => ['source' => 'feed'],
            'published_at' => now()->subHour()->toISOString(),
            'discovered_at' => now()->subMinutes(30)->toISOString(),
            'created_at' => now()->subMinutes(30)->toISOString(),
            'updated_at' => now()->toISOString(),
            'category' => [
                'id' => 5,
                'name' => 'AI Tools',
                'slug' => 'ai-tools',
            ],
            'source' => [
                'id' => 9,
                'name' => 'Example Feed',
                'slug' => 'example-feed',
                'kind' => 'rss',
                'base_url' => 'https://example.com',
                'trust_score' => 88,
            ],
            'latest_score' => [
                'id' => 10,
                'relevance_score' => 10,
                'freshness_score' => 11,
                'credibility_score' => 12,
                'pillar_fit_score' => 13,
                'evergreen_potential_score' => 9,
                'novelty_score' => 8,
                'business_value_score' => 10,
                'total_score' => 73,
                'decision' => 'knowledge_base',
                'reasoning' => 'Useful reference value.',
                'scored_at' => now()->subMinutes(10)->toISOString(),
            ],
            'latest_extraction' => [
                'id' => 20,
                'extractor' => 'default',
                'excerpt' => 'Important excerpt.',
                'facts_json' => [],
                'entities_json' => [],
                'claims_json' => [],
                'metadata' => [],
                'extracted_at' => now()->subMinutes(5)->toISOString(),
            ],
            'latest_route' => [
                'id' => 30,
                'route' => 'knowledge_base',
                'knowledge_base_entry_id' => 2,
                'content_topic_id' => null,
                'post_id' => null,
                'metadata' => [],
                'routed_at' => now()->subMinute()->toISOString(),
                'knowledge_base_entry' => [
                    'id' => 2,
                    'title' => 'Reference entry',
                    'slug' => 'reference-entry',
                ],
                'content_topic' => null,
                'post' => null,
            ],
        ], $overrides);
    }
}
