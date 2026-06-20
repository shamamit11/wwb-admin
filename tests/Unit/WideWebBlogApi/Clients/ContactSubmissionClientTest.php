<?php

namespace Tests\Unit\WideWebBlogApi\Clients;

use App\Services\WideWebBlogApi\Clients\ContactSubmissionClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactSubmissionClientTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_it_can_load_and_update_contact_submissions(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/contact-submissions') {
                return Http::response([
                    'data' => [
                        ['id' => 'sub_1', 'name' => 'Amit Sharma', 'email' => 'amit@example.com', 'topic' => 'Partnership', 'message' => 'Hello', 'status' => 'new', 'admin_notes' => null, 'metadata' => [], 'submitted_at' => '2026-06-20T10:00:00Z', 'reviewed_at' => null, 'reviewed_by' => null, 'created_at' => '2026-06-20T10:00:00Z', 'updated_at' => '2026-06-20T10:00:00Z'],
                    ],
                ], 200);
            }

            if ($request->method() === 'GET' && $request->url() === $this->apiBaseUrl.'/admin/contact-submissions/sub_1') {
                return Http::response([
                    'data' => ['id' => 'sub_1', 'name' => 'Amit Sharma', 'email' => 'amit@example.com', 'topic' => 'Partnership', 'message' => 'Hello', 'status' => 'new', 'admin_notes' => null, 'metadata' => ['source' => 'affiliate'], 'submitted_at' => '2026-06-20T10:00:00Z', 'reviewed_at' => null, 'reviewed_by' => null, 'created_at' => '2026-06-20T10:00:00Z', 'updated_at' => '2026-06-20T10:00:00Z'],
                ], 200);
            }

            if ($request->method() === 'PATCH' && $request->url() === $this->apiBaseUrl.'/admin/contact-submissions/sub_1') {
                $this->assertSame('read', $request['status']);
                $this->assertSame('Handled.', $request['admin_notes']);
                $this->assertSame(['source' => 'affiliate'], $request['metadata']);

                return Http::response([
                    'data' => ['id' => 'sub_1', 'status' => 'read', 'admin_notes' => 'Handled.', 'metadata' => ['source' => 'affiliate'], 'updated_at' => '2026-06-20T11:00:00Z'],
                ], 200);
            }

            return Http::response(['message' => 'Unexpected request.'], 500);
        });

        $client = app(ContactSubmissionClient::class);

        $index = $client->index('test-token', 'Bearer');
        $show = $client->show('test-token', 'Bearer', 'sub_1');
        $updated = $client->update('test-token', 'Bearer', 'sub_1', [
            'status' => 'read',
            'admin_notes' => 'Handled.',
            'metadata' => ['source' => 'affiliate'],
        ]);

        $this->assertSame('Amit Sharma', $index['data'][0]['name']);
        $this->assertSame('sub_1', $show['data']['id']);
        $this->assertSame('read', $updated['data']['status']);
    }
}
