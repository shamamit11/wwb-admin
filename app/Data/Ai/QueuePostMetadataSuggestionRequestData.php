<?php

namespace App\Data\Ai;

class QueuePostMetadataSuggestionRequestData
{
    public function __construct(
        public readonly ?string $instructions,
        public readonly ?string $promptTemplateKey,
    ) {
    }

    public function toArray(): array
    {
        return [
            'instructions' => $this->instructions,
            'prompt_template_key' => $this->promptTemplateKey,
        ];
    }
}
