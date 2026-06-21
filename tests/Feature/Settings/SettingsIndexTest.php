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
            ->assertSee('Open Site Settings')
            ->assertSee('General')
            ->assertSee('Publishing')
            ->assertSee('Storage')
            ->assertSee('AI')
            ->assertSee('Integrations')
            ->assertSee('The only writable service-backed settings flow currently available here is the dedicated Site Settings footer editor.')
            ->assertSee('Footer-wide brand, social link, and legal link management now lives in the dedicated Site Settings singleton editor.')
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
            ->assertSee('Standard prompt content is managed in the dedicated prompt screens')
            ->assertSee('sensitive provider secrets should not appear here');
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
