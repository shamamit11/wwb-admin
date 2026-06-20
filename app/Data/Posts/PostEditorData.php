<?php

namespace App\Data\Posts;

use Illuminate\Support\Arr;

class PostEditorData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $excerpt,
        public readonly string $categoryId,
        public readonly string $templateId,
        public readonly string $featuredMediaId,
        public readonly string $status,
        public readonly string $visibility,
        public readonly string $publishedAt,
        public readonly string $scheduledFor,
        public readonly ?int $contentVersion,
        public readonly ?int $readingTimeMinutes,
        public readonly ?int $wordCount,
        public readonly bool $isFeatured,
        public readonly string $metaJson,
        public readonly array $meta,
        public readonly array $tagIds,
        public readonly array $blocks,
        public readonly ?string $canonicalUrl,
        public readonly bool $isAiGenerated,
        public readonly ?int $sourceContentBriefId,
        public readonly ?int $sourceContentTopicId,
        public readonly ?int $generatedByAiJobId,
        public readonly ?string $generatedBy,
    ) {
    }

    public static function fromApi(array $post): self
    {
        $blocks = collect(Arr::get($post, 'blocks', []))
            ->values()
            ->map(fn (array $block, int $index): array => PostBlockData::fromApi($block, $index + 1)->toEditorState())
            ->all();

        return new self(
            id: Arr::get($post, 'id'),
            title: (string) Arr::get($post, 'title', ''),
            slug: (string) Arr::get($post, 'slug', ''),
            excerpt: (string) (Arr::get($post, 'excerpt') ?? ''),
            categoryId: Arr::get($post, 'category.id') ? (string) Arr::get($post, 'category.id') : '',
            templateId: Arr::get($post, 'template.id') ? (string) Arr::get($post, 'template.id') : '',
            featuredMediaId: Arr::get($post, 'featured_media.id') ? (string) Arr::get($post, 'featured_media.id') : '',
            status: (string) Arr::get($post, 'status', 'draft'),
            visibility: (string) Arr::get($post, 'visibility', 'public'),
            publishedAt: self::datetimeInput(Arr::get($post, 'published_at')),
            scheduledFor: self::datetimeInput(Arr::get($post, 'scheduled_for')),
            contentVersion: Arr::get($post, 'content_version'),
            readingTimeMinutes: Arr::get($post, 'reading_time_minutes'),
            wordCount: Arr::get($post, 'word_count'),
            isFeatured: (bool) Arr::get($post, 'is_featured', false),
            metaJson: self::jsonPayload(Arr::get($post, 'meta')),
            meta: is_array(Arr::get($post, 'meta')) ? Arr::get($post, 'meta') : [],
            tagIds: collect(Arr::get($post, 'tags', []))
                ->pluck('id')
                ->filter()
                ->map(fn (mixed $id): string => (string) $id)
                ->values()
                ->all(),
            blocks: $blocks !== [] ? $blocks : [PostBlockData::blank(1)->toEditorState()],
            canonicalUrl: Arr::get($post, 'canonical_url'),
            isAiGenerated: (bool) Arr::get($post, 'is_ai_generated', false),
            sourceContentBriefId: Arr::get($post, 'source_content_brief_id'),
            sourceContentTopicId: Arr::get($post, 'source_content_topic_id'),
            generatedByAiJobId: Arr::get($post, 'generated_by_ai_job_id'),
            generatedBy: Arr::get($post, 'generated_by'),
        );
    }

    public static function blank(): self
    {
        return new self(
            id: null,
            title: '',
            slug: '',
            excerpt: '',
            categoryId: '',
            templateId: '',
            featuredMediaId: '',
            status: 'draft',
            visibility: 'public',
            publishedAt: '',
            scheduledFor: '',
            contentVersion: null,
            readingTimeMinutes: null,
            wordCount: null,
            isFeatured: false,
            metaJson: '',
            meta: [],
            tagIds: [],
            blocks: [PostBlockData::blank(1)->toEditorState()],
            canonicalUrl: null,
            isAiGenerated: false,
            sourceContentBriefId: null,
            sourceContentTopicId: null,
            generatedByAiJobId: null,
            generatedBy: null,
        );
    }

    public static function datetimeInput(mixed $value): string
    {
        if (! is_string($value) || $value === '') {
            return '';
        }

        try {
            return now()->parse($value)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return '';
        }
    }

    public static function jsonPayload(mixed $value): string
    {
        if (! is_array($value) || $value === []) {
            return '';
        }

        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '';
    }
}
