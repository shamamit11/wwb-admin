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
                'title' => 'AI Content',
                'items' => [
                    $this->item('Topic Queue', 'topic-queue.index', 'Review suggested topics, approve editorial directions, and manage topic status.', false, 'queue'),
                    $this->item('Content Briefs', 'content-briefs.index', 'Review, edit, approve, and promote generated briefs toward draft creation.', false, 'document'),
                    $this->item('Prompt Templates', 'ai-prompts.index', 'Manage AI prompt templates, version history, and active generation prompts.', false, 'document'),
                    $this->item('AI Jobs', 'ai-jobs.index', 'Monitor AI workflow execution, failures, and retryable jobs.', false, 'spark'),
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
