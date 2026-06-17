<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;

class SettingsIndexTest extends TestCase
{
    public function test_settings_screen_is_read_only_and_honest_about_missing_service_backed_settings(): void
    {
        $response = $this->withSession($this->authenticatedSession())
            ->get(route('settings.index'));

        $response
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee('This screen is read-only until broader settings endpoints exist in the service contract.')
            ->assertSee('General')
            ->assertSee('Publishing')
            ->assertSee('Storage')
            ->assertSee('AI')
            ->assertSee('Integrations')
            ->assertSee('No service-backed settings endpoint exists yet for broad admin configuration.')
            ->assertDontSee('Screen scaffolded');
    }

    public function test_settings_screen_can_render_ai_placeholder_tab_without_fake_controls(): void
    {
        $response = $this->withSession($this->authenticatedSession())
            ->get(route('settings.index', ['tab' => 'ai']));

        $response
            ->assertOk()
            ->assertSee('AI settings are not service-backed yet')
            ->assertSee('Await Service Contract')
            ->assertSee('Sensitive provider secrets should not appear in this admin until a dedicated secure flow exists.');
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
}
