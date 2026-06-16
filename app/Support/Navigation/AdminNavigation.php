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
                    $this->item('Dashboard', 'dashboard', 'Action-oriented operational overview.'),
                ],
            ],
            [
                'title' => 'Publishing',
                'items' => [
                    $this->item('Posts', 'posts.index', 'Manage post inventory, status, and editorial actions.'),
                    $this->item('Categories', 'categories.index', 'Table-first category structure and taxonomy operations.'),
                    $this->item('Tags', 'tags.index', 'Tag taxonomy and editorial labeling.'),
                    $this->item('Media Library', 'media.index', 'Uploaded assets, metadata, and usage review.'),
                    $this->item('Templates', 'templates.index', 'Structured template definitions and previews.'),
                    $this->item('Knowledge Base', 'knowledge-base.index', 'Editorial knowledge entries and linked context.'),
                ],
            ],
            [
                'title' => 'Operations',
                'items' => [
                    $this->item('SEO', 'seo.index', 'Metadata, score, schema, sitemap, and feed visibility.'),
                    $this->item('Settings', 'settings.index', 'Scoped operational settings and placeholders.'),
                ],
            ],
            [
                'title' => 'Roadmap',
                'items' => [
                    $this->item('Topic Queue', 'topic-queue.index', 'Planned review queue once APIs exist.', true),
                    $this->item('AI Jobs', 'ai-jobs.index', 'Planned AI job monitoring once APIs exist.', true),
                ],
            ],
        ];
    }

    protected function item(string $label, string $route, string $description, bool $placeholder = false): array
    {
        return [
            'label' => $label,
            'route' => $route,
            'active' => $route,
            'description' => $description,
            'placeholder' => $placeholder,
        ];
    }
}
