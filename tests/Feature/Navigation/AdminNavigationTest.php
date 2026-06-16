<?php

namespace Tests\Feature\Navigation;

use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    public function test_authenticated_admin_can_access_mvp_placeholder_routes(): void
    {
        $session = $this->authenticatedSession();

        foreach ([
            'posts.index',
            'categories.index',
            'tags.index',
            'media.index',
            'templates.index',
            'knowledge-base.index',
            'seo.index',
            'settings.index',
        ] as $route) {
            $this->withSession($session)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_sidebar_navigation_highlights_the_current_section(): void
    {
        $response = $this->withSession($this->authenticatedSession())
            ->get(route('categories.index'));

        $response
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Publishing')
            ->assertSee('Operations')
            ->assertSee('Roadmap')
            ->assertSee('href="'.route('categories.index').'"', false)
            ->assertSee('bg-[var(--color-accent-soft)]', false);
    }

    public function test_roadmap_modules_are_rendered_as_placeholders(): void
    {
        $response = $this->withSession($this->authenticatedSession())
            ->get(route('topic-queue.index'));

        $response
            ->assertOk()
            ->assertSee('Roadmap placeholder')
            ->assertSee('API Not Available Yet')
            ->assertSee('Placeholder');
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
