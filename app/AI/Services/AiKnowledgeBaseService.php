<?php

namespace App\AI\Services;

use App\AI\Contracts\AiKnowledgeBaseInterface;
use App\AI\Models\AiKnowledgeDomain;
use App\AI\Models\AiKnowledgeGap;
use App\AI\Models\AiKnowledgeItem;
use App\AI\Models\AiKnowledgeSource;
use App\AI\Models\AiKnowledgeTopic;
use Illuminate\Database\Eloquent\Builder;

class AiKnowledgeBaseService implements AiKnowledgeBaseInterface
{
    public function search(string $query, array $options = []): array
    {
        $limit = $options['limit'] ?? 5;
        $domainFilter = $options['domain'] ?? null;
        $memoryTypeFilter = $options['memory_type'] ?? null;
        $levelFilter = $options['level'] ?? null;

        $cleanQuery = trim($query);
        if ($cleanQuery === '') {
            return [
                'items' => [],
                'total' => 0,
                'domain_matched' => null,
            ];
        }

        // Extract meaningful tokens
        $tokens = array_values(array_filter(
            explode(' ', mb_strtolower($cleanQuery, 'UTF-8')),
            fn ($t) => mb_strlen($t) > 2
        ));

        $itemQuery = AiKnowledgeItem::query()
            ->active()
            ->with(['topic.domain', 'source']);

        if ($domainFilter !== null) {
            $itemQuery->whereHas('topic.domain', function (Builder $q) use ($domainFilter) {
                $q->where('code', $domainFilter);
            });
        }

        if ($memoryTypeFilter !== null) {
            $itemQuery->where('memory_type', $memoryTypeFilter);
        }

        if ($levelFilter !== null) {
            $itemQuery->whereHas('topic', function (Builder $q) use ($levelFilter) {
                $q->where('level', $levelFilter);
            });
        }

        // Multi-field lexical matching
        $itemQuery->where(function (Builder $q) use ($cleanQuery, $tokens) {
            $q->where('title', 'like', "%{$cleanQuery}%")
                ->orWhere('concept', 'like', "%{$cleanQuery}%")
                ->orWhere('implementation_patterns', 'like', "%{$cleanQuery}%");

            foreach ($tokens as $token) {
                $q->orWhere('title', 'like', "%{$token}%")
                    ->orWhere('concept', 'like', "%{$token}%")
                    ->orWhereHas('topic', fn ($tq) => $tq->where('name', 'like', "%{$token}%"))
                    ->orWhereHas('topic.domain', fn ($dq) => $dq->where('name', 'like', "%{$token}%"));
            }
        });

        $results = $itemQuery->take($limit * 2)->get();

        // Score results
        $scored = $results->map(function (AiKnowledgeItem $item) use ($tokens, $cleanQuery) {
            $score = 0.0;
            $haystack = mb_strtolower(
                $item->title.' '.$item->concept.' '.($item->implementation_patterns ?? '').' '.$item->topic->name.' '.$item->topic->domain->name,
                'UTF-8'
            );

            // Exact phrase match bonus
            if (str_contains($haystack, mb_strtolower($cleanQuery, 'UTF-8'))) {
                $score += 3.0;
            }

            foreach ($tokens as $token) {
                if (str_contains($haystack, $token)) {
                    $score += 1.0;
                }
            }

            // Source authority boost
            $authority = $item->source?->authority_score ?? 0.85;
            $score *= (float) $authority;

            return [
                'id' => $item->id,
                'title' => $item->title,
                'domain_code' => $item->topic->domain->code,
                'domain_name' => $item->topic->domain->name,
                'topic_name' => $item->topic->name,
                'topic_slug' => $item->topic->slug,
                'level' => $item->topic->level,
                'memory_type' => $item->memory_type,
                'concept' => $item->concept,
                'rules' => $item->rules ?? [],
                'workflows' => $item->workflows ?? [],
                'implementation_patterns' => $item->implementation_patterns,
                'examples' => $item->examples,
                'common_problems' => $item->common_problems,
                'source_citation' => $item->source_citation ?: ($item->source?->title ?? 'E-commerce Architecture Standards'),
                'source_url' => $item->source?->url,
                'confidence' => (float) $item->confidence,
                'version' => $item->version,
                'updated_at' => $item->updated_at->toIso8601String(),
                'relevance_score' => round($score, 2),
            ];
        })
            ->filter(function (array $item) use ($tokens) {
                $minScore = count($tokens) >= 3 ? 1.5 : 0.7;

                return $item['relevance_score'] >= $minScore;
            })
            ->sortByDesc('relevance_score')
            ->values()
            ->take($limit)
            ->all();

        $domainMatched = ! empty($scored) ? $scored[0]['domain_code'] : null;

        // Automatic Knowledge Gap recording when confidence is low or zero results found
        if (empty($scored)) {
            $this->recordKnowledgeGap(
                $cleanQuery,
                $domainFilter,
                'Zero matching verified knowledge items found during search query.'
            );
        }

        return [
            'items' => $scored,
            'total' => count($scored),
            'domain_matched' => $domainMatched,
        ];
    }

    public function retrieveByDomain(string $domainCode, int $limit = 10): array
    {
        $domain = AiKnowledgeDomain::where('code', $domainCode)->first();
        if (! $domain) {
            return [];
        }

        return $domain->items()
            ->active()
            ->with(['topic', 'source'])
            ->take($limit)
            ->get()
            ->map(fn (AiKnowledgeItem $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'domain_code' => $domain->code,
                'domain_name' => $domain->name,
                'topic_name' => $item->topic->name,
                'level' => $item->topic->level,
                'memory_type' => $item->memory_type,
                'concept' => $item->concept,
                'rules' => $item->rules ?? [],
                'workflows' => $item->workflows ?? [],
                'implementation_patterns' => $item->implementation_patterns,
                'source_citation' => $item->source_citation ?: ($item->source?->title ?? 'E-commerce Standard'),
                'confidence' => (float) $item->confidence,
            ])
            ->all();
    }

    public function retrieveByMemoryType(string $memoryType, int $limit = 10): array
    {
        return AiKnowledgeItem::query()
            ->active()
            ->where('memory_type', $memoryType)
            ->with(['topic.domain', 'source'])
            ->take($limit)
            ->get()
            ->map(fn (AiKnowledgeItem $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'domain_code' => $item->topic->domain->code,
                'domain_name' => $item->topic->domain->name,
                'topic_name' => $item->topic->name,
                'memory_type' => $item->memory_type,
                'concept' => $item->concept,
                'rules' => $item->rules ?? [],
                'implementation_patterns' => $item->implementation_patterns,
                'source_citation' => $item->source_citation,
            ])
            ->all();
    }

    public function recordKnowledgeGap(string $query, ?string $domainCode = null, string $reason = ''): AiKnowledgeGap
    {
        $domainId = null;
        if ($domainCode !== null) {
            $domain = AiKnowledgeDomain::where('code', $domainCode)->first();
            $domainId = $domain?->id;
        }

        return AiKnowledgeGap::firstOrCreate(
            [
                'query_or_topic' => mb_substr($query, 0, 190),
                'status' => 'identified',
            ],
            [
                'domain_id' => $domainId,
                'description' => $reason ?: 'Missing or unverified domain knowledge required to answer user query accurately.',
                'recommended_action' => 'Ingest official documentation, standard specifications, or developer notes on this topic.',
                'identified_at' => now(),
            ]
        );
    }

    public function getStats(): array
    {
        return [
            'total_domains' => AiKnowledgeDomain::count(),
            'total_topics' => AiKnowledgeTopic::count(),
            'verified_topics' => AiKnowledgeTopic::where('verification_status', 'verified')->count(),
            'total_items' => AiKnowledgeItem::where('is_active', true)->count(),
            'total_sources' => AiKnowledgeSource::count(),
            'pending_gaps' => AiKnowledgeGap::pending()->count(),
        ];
    }
}
