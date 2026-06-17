<?php

namespace Tests\Integration;

use App\Services\WideWebBlogApi\Clients\TemplateClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TemplateClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_template_client_uses_documented_crud_endpoints_and_payloads(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/templates' => Http::response(['data' => []], 200),
            $this->apiBaseUrl.'/admin/templates/10' => Http::response(['data' => ['id' => 10]], 200),
            $this->apiBaseUrl.'/admin/templates/11' => Http::response(['data' => ['id' => 11]], 200),
            $this->apiBaseUrl.'/admin/templates/12' => Http::response([], 204),
        ]);

        $client = app(TemplateClient::class);

        $client->index('test-token');
        $client->show('test-token', 'Bearer', 10);
        $client->store('test-token', 'Bearer', [
            'name' => 'Tutorial',
            'slug' => 'tutorial',
            'template_type' => 'tutorial',
            'description' => 'Step-by-step structure.',
            'status' => 'active',
            'default_excerpt_prompt' => 'Summarize the key steps.',
            'default_meta' => [
                'recommended_sections' => ['introduction', 'steps', 'faq'],
            ],
            'blocks' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'label' => 'Title',
                    'default_markdown' => '# {{title}}',
                    'settings' => ['level' => 1],
                    'is_required' => true,
                ],
            ],
        ]);
        $client->update('test-token', 'Bearer', 11, [
            'name' => 'Comparison',
            'slug' => 'comparison',
            'template_type' => 'comparison',
            'description' => 'Tradeoff-oriented structure.',
            'status' => 'draft',
            'default_excerpt_prompt' => 'Summarize the recommendation.',
            'default_meta' => [
                'recommended_sections' => ['problem', 'options', 'recommendation'],
            ],
            'blocks' => [
                [
                    'block_type' => 'callout',
                    'sort_order' => 2,
                    'label' => 'Decision summary',
                    'default_markdown' => 'Lead with the final recommendation.',
                    'settings' => ['variant' => 'info'],
                    'is_required' => false,
                ],
            ],
        ]);
        $client->delete('test-token', 'Bearer', 12);

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === $this->apiBaseUrl.'/admin/templates'
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === $this->apiBaseUrl.'/admin/templates/10';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === $this->apiBaseUrl.'/admin/templates'
                && $request['template_type'] === 'tutorial'
                && $request['blocks'][0]['block_type'] === 'heading'
                && $request['blocks'][0]['settings']['level'] === 1;
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'PUT'
                && $request->url() === $this->apiBaseUrl.'/admin/templates/11'
                && $request['status'] === 'draft'
                && $request['blocks'][0]['block_type'] === 'callout'
                && $request['default_meta']['recommended_sections'][2] === 'recommendation';
        });

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'DELETE'
                && $request->url() === $this->apiBaseUrl.'/admin/templates/12';
        });
    }
}
