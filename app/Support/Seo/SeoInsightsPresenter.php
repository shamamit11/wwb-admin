<?php

namespace App\Support\Seo;

class SeoInsightsPresenter
{
    public function scoreValue(array $payload): ?int
    {
        $score = $payload['total_score'] ?? null;

        return is_numeric($score) ? (int) $score : null;
    }

    public function scoreGrade(array $payload): ?string
    {
        $grade = $payload['grade'] ?? null;

        return is_string($grade) && $grade !== ''
            ? str($grade)->replace('_', ' ')->headline()->value()
            : null;
    }

    public function scoreSubscores(array $payload): array
    {
        $subscores = $payload['subscores'] ?? [];

        if (! is_array($subscores)) {
            return [];
        }

        return collect($subscores)
            ->filter(fn (mixed $section, mixed $key): bool => is_array($section) && is_string($key))
            ->map(function (array $section, string $key): array {
                $score = $section['score'] ?? null;
                $maxScore = $section['max_score'] ?? null;
                $suggestionCount = $section['suggestion_count'] ?? null;

                return [
                    'key' => $key,
                    'label' => str($key)->replace('_', ' ')->headline()->value(),
                    'score' => is_numeric($score) ? (int) $score : null,
                    'max_score' => is_numeric($maxScore) ? (int) $maxScore : null,
                    'suggestion_count' => is_numeric($suggestionCount) ? (int) $suggestionCount : null,
                ];
            })
            ->values()
            ->all();
    }

    public function recommendations(array $payload): array
    {
        $recommendations = $payload['recommendations'] ?? [];

        if (! is_array($recommendations)) {
            return [];
        }

        return collect($recommendations)
            ->filter(fn (mixed $item): bool => is_string($item) && trim($item) !== '')
            ->values()
            ->all();
    }

    public function schemaSummary(array $payload): array
    {
        $graph = $payload['@graph'] ?? [];
        $graphItems = is_array($graph) ? array_values(array_filter($graph, 'is_array')) : [];

        return [
            'context' => is_string($payload['@context'] ?? null) ? $payload['@context'] : null,
            'graph_count' => count($graphItems),
            'graph_types' => collect($graphItems)
                ->map(fn (array $item): ?string => is_string($item['@type'] ?? null) ? $item['@type'] : null)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    public function prettySchema(array $payload): string
    {
        if ($payload === []) {
            return '';
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '';
    }
}
