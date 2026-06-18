<?php

namespace App\Data\Ai;

class GenerateDraftRequestData
{
    public function __construct(
        public readonly int $categoryId,
        public readonly ?int $templateId,
        public readonly string $visibility,
        public readonly ?string $promptTemplateKey,
        public readonly ?string $generationMode,
    ) {
    }

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'template_id' => $this->templateId,
            'visibility' => $this->visibility,
            'prompt_template_key' => $this->promptTemplateKey,
            'generation_mode' => $this->generationMode,
        ];
    }
}
