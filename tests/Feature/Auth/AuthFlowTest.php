<?php

namespace Tests\Feature\Auth;

use App\Livewire\Admin\Auth\Login;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    protected string $apiBaseUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiBaseUrl = rtrim(config('widewebblog.api.base_url'), '/');
    }

    public function test_guests_are_redirected_to_login_from_dashboard(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }

    public function test_login_screen_is_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Admin Sign In');
    }

    public function test_login_stores_bearer_token_and_admin_user_in_session(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/auth/login' => Http::response([
                'data' => [
                    'token' => 'test-token',
                    'token_type' => 'Bearer',
                    'abilities' => 'admin',
                ],
            ], 200),
            $this->apiBaseUrl.'/admin/me' => Http::response([
                'data' => [
                    'id' => 1,
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'is_admin' => true,
                ],
            ], 200),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@example.com')
            ->set('password', 'secret-password')
            ->call('authenticate')
            ->assertRedirect(config('widewebblog.auth.home_path'));

        $this->assertSame('test-token', session(config('widewebblog.session.token_key')));
        $this->assertSame('Bearer', session(config('widewebblog.session.token_type_key')));
        $this->assertSame('Admin User', session(config('widewebblog.session.user_key'))['name']);
    }

    public function test_invalid_credentials_return_a_generic_error(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/auth/login' => Http::response([
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 422),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'wrong@example.com')
            ->set('password', 'bad-password')
            ->call('authenticate')
            ->assertHasErrors(['email']);

        $this->assertNull(session(config('widewebblog.session.token_key')));
    }

    public function test_authenticated_session_can_access_dashboard(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/posts*' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $response = $this
            ->withSession([
                config('widewebblog.session.token_key') => 'test-token',
                config('widewebblog.session.token_type_key') => 'Bearer',
                config('widewebblog.session.user_key') => [
                    'id' => 1,
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                ],
            ])
            ->get('/');

        $response
            ->assertOk()
            ->assertSee('Search the admin')
            ->assertSee('Operations Console')
            ->assertSee('Browse Admin');
    }

    public function test_unauthorized_admin_session_is_redirected_to_forbidden(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/admin/me' => Http::response([
                'message' => 'This action is unauthorized.',
            ], 403),
        ]);

        $response = $this
            ->withSession([
                config('widewebblog.session.token_key') => 'test-token',
                config('widewebblog.session.token_type_key') => 'Bearer',
            ])
            ->get('/');

        $response->assertRedirect(route('auth.forbidden'));
        $this->assertNull(session(config('widewebblog.session.token_key')));
    }

    public function test_logout_clears_local_session_state(): void
    {
        Http::fake([
            $this->apiBaseUrl.'/auth/logout' => Http::response([
                'message' => 'Logged out.',
            ], 200),
        ]);

        $response = $this
            ->withSession([
                config('widewebblog.session.token_key') => 'test-token',
                config('widewebblog.session.token_type_key') => 'Bearer',
                config('widewebblog.session.user_key') => [
                    'id' => 1,
                    'name' => 'Admin User',
                ],
            ])
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertNull(session(config('widewebblog.session.token_key')));
        $this->assertNull(session(config('widewebblog.session.user_key')));
    }

    public function test_login_screen_renders_guest_layout_regions(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Editorial control for a modern publishing desk.')
            ->assertSee('CMS Administrator Portal')
            ->assertSee('Admin Sign In');
    }
}
