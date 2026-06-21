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
                'title' => 'CMS',
                'items' => [
                    $this->item('Homepage', 'homepage.index', 'Structured homepage curation and section management.', false, 'homepage'),
                    $this->item('About Us', 'about-page.index', 'Singleton About Page editor backed by the dedicated service contract.', false, 'document'),
                    $this->item('Contact Page', 'contact-page.index', 'Singleton Contact Page editor backed by the dedicated service contract.', false, 'document'),
                    $this->item('Pages', 'pages.index', 'Manage static and evergreen service-backed pages.', false, 'pages'),
                ],
            ],
            [
                'title' => 'Publishing',
                'items' => [
                    $this->item('Posts', 'posts.index', 'Manage post inventory, status, and editorial actions.', false, 'posts'),
                    $this->item('Categories', 'categories.index', 'Table-first category structure and taxonomy operations.', false, 'categories'),
                    $this->item('Tags', 'tags.index', 'Tag taxonomy and editorial labeling.', false, 'tags'),
                    $this->item('Media Library', 'media.index', 'Uploaded assets, metadata, and usage review.', false, 'media'),
                    $this->item('Knowledge Base', 'knowledge-base.index', 'Editorial knowledge entries and linked context.', false, 'knowledge'),
                ],
            ],
            [
                'title' => 'Operations',
                'items' => [
                    $this->item('SEO', 'seo.index', 'Metadata, score, schema, sitemap, and feed visibility.', false, 'seo'),
                    $this->item('Contact Submissions', 'contact-submissions.index', 'Review inbound contact form messages, update status, and capture internal notes.', false, 'queue'),
                    $this->item('Site Settings', 'site-settings.index', 'Singleton site settings editor for footer-wide brand, social, and legal footer links.', false, 'settings'),
                    $this->item('Settings', 'settings.index', 'Scoped operational settings and placeholders.', false, 'settings'),
                ],
            ],
            [
                'title' => 'AI Content',
                'items' => [
                    $this->item('Topic Queue', 'topic-queue.index', 'Monitor topic scores and automation thresholds before draft generation takes over.', false, 'queue'),
                    $this->item('Draft Review', 'draft-review.index', 'Review AI-generated draft posts, validate source context, and publish manually when ready.', false, 'posts'),
                    $this->item('Standard Prompts', 'ai-prompts.index', 'Manage the two versioned standard prompts that drive topic and blog generation.', false, 'document'),
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
