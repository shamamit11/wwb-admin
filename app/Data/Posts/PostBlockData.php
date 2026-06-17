<?php

namespace App\Data\Posts;

use Illuminate\Support\Arr;

class PostBlockData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $key,
        public readonly string $blockType,
        public readonly int $sortOrder,
        public readonly string $contentText,
        public readonly ?int $sourceTemplateBlockId,
    ) {
    }

    public static function fromApi(array $block, int $fallbackOrder): self
    {
        return new self(
            id: Arr::get($block, 'id'),
            key: (string) (Arr::get($block, 'block_key') ?: 'block-'.str()->uuid()),
            blockType: (string) Arr::get($block, 'block_type', 'paragraph'),
            sortOrder: (int) Arr::get($block, 'sort_order', $fallbackOrder),
            contentText: (string) (Arr::get($block, 'content_markdown')
                ?? Arr::get($block, 'plain_text_cache')
                ?? ''),
            sourceTemplateBlockId: Arr::get($block, 'source_template_block_id'),
        );
    }

    public static function blank(int $sortOrder, string $blockType = 'paragraph'): self
    {
        return new self(
            id: null,
            key: 'block-'.str()->uuid(),
            blockType: $blockType,
            sortOrder: $sortOrder,
            contentText: '',
            sourceTemplateBlockId: null,
        );
    }

    public function toEditorState(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'blockType' => $this->blockType,
            'sortOrder' => $this->sortOrder,
            'contentText' => $this->contentText,
            'sourceTemplateBlockId' => $this->sourceTemplateBlockId ? (string) $this->sourceTemplateBlockId : '',
        ];
    }

    public static function payloadFromEditorState(array $state, int $sortOrder): array
    {
        $content = preg_split('/\R/u', trim((string) ($state['contentText'] ?? ''))) ?: [];
        $content = array_values(array_filter(array_map('trim', $content), static fn (string $line): bool => $line !== ''));

        if ($content === []) {
            $content = [trim((string) ($state['contentText'] ?? ''))];
        }

        return [
            'block_type' => (string) ($state['blockType'] ?? 'paragraph'),
            'sort_order' => $sortOrder,
            'content' => $content,
            'source_template_block_id' => filled($state['sourceTemplateBlockId'] ?? null)
                ? (int) $state['sourceTemplateBlockId']
                : null,
        ];
    }
}
