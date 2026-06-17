<?php

namespace App\Support\Navigation;

class AdminNavigation
{
    public function sections(): array
    {
        return [
            [
                'title' => 'Overview',
                'items' => [
                    $this->item('Dashboard', 'dashboard', 'Action-oriented operational overview.', false, 'dashboard'),
                ],
            ],
            [
                'title' => 'Publishing',
                'items' => [
                    $this->item('Homepage', 'homepage.index', 'Structured homepage curation and section management.', false, 'homepage'),
                    $this->item('Posts', 'posts.index', 'Manage post inventory, status, and editorial actions.', false, 'posts'),
                    $this->item('Pages', 'pages.index', 'Manage static and evergreen service-backed pages.', false, 'pages'),
                    $this->item('Categories', 'categories.index', 'Table-first category structure and taxonomy operations.', false, 'categories'),
                    $this->item('Tags', 'tags.index', 'Tag taxonomy and editorial labeling.', false, 'tags'),
                    $this->item('Media Library', 'media.index', 'Uploaded assets, metadata, and usage review.', false, 'media'),
                    $this->item('Templates', 'templates.index', 'Structured template definitions and previews.', false, 'templates'),
                    $this->item('Knowledge Base', 'knowledge-base.index', 'Editorial knowledge entries and linked context.', false, 'knowledge'),
                ],
            ],
            [
                'title' => 'Operations',
                'items' => [
                    $this->item('SEO', 'seo.index', 'Metadata, score, schema, sitemap, and feed visibility.', false, 'seo'),
                    $this->item('Settings', 'settings.index', 'Scoped operational settings and placeholders.', false, 'settings'),
                ],
            ],
            [
                'title' => 'Roadmap',
                'items' => [
                    $this->item('Topic Queue', 'topic-queue.index', 'Planned review queue once APIs exist.', true, 'queue'),
                    $this->item('AI Jobs', 'ai-jobs.index', 'Planned AI job monitoring once APIs exist.', true, 'spark'),
                ],
            ],
        ];
    }

    protected function item(string $label, string $route, string $description, bool $placeholder = false, string $icon = 'square'): array
    {
        return [
            'label' => $label,
            'route' => $route,
            'active' => $route,
            'description' => $description,
            'placeholder' => $placeholder,
            'icon' => $icon,
        ];
    }
}
