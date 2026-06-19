<?php

namespace App\Data\Ai;

use Illuminate\Support\Arr;

class QueuedAiJobData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $type,
        public readonly string $status,
    ) {
    }

    public static function fromApi(array $payload): self
    {
        $id = Arr::get($payload, 'id');

        return new self(
            id: is_int($id) ? $id : (is_string($id) && ctype_digit($id) ? (int) $id : null),
            type: (string) Arr::get($payload, 'type', ''),
            status: (string) Arr::get($payload, 'status', ''),
        );
    }
}
