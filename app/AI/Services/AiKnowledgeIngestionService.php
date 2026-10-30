<?php

namespace App\AI\Services;

use App\AI\Contracts\AiKnowledgeIngestionInterface;
use App\AI\Knowledge\AiCurriculumRegistry;
use App\AI\Models\AiKnowledgeDomain;
use App\AI\Models\AiKnowledgeItem;
use App\AI\Models\AiKnowledgeSource;
use App\AI\Models\AiKnowledgeTopic;

class AiKnowledgeIngestionService implements AiKnowledgeIngestionInterface
{
    public function ingestDomain(array $domainData): AiKnowledgeDomain
    {
        return AiKnowledgeDomain::updateOrCreate(
            ['code' => $domainData['code']],
            [
                'name' => $domainData['name'],
                'description' => $domainData['description'],
                'priority' => $domainData['priority'] ?? 100,
                'is_active' => true,
                'metadata' => $domainData['metadata'] ?? null,
            ]
        );
    }

    public function ingestTopic(string $domainCode, array $topicData): AiKnowledgeTopic
    {
        $domain = AiKnowledgeDomain::where('code', $domainCode)->firstOrFail();

        $parentId = null;
        if (! empty($topicData['parent_slug'])) {
            $parent = AiKnowledgeTopic::where('slug', $topicData['parent_slug'])->first();
            $parentId = $parent?->id;
        }

        return AiKnowledgeTopic::updateOrCreate(
            ['slug' => $topicData['slug']],
            [
                'domain_id' => $domain->id,
                'parent_id' => $parentId,
                'name' => $topicData['name'],
                'description' => $topicData['description'] ?? null,
                'level' => $topicData['level'] ?? 'beginner',
                'verification_status' => $topicData['verification_status'] ?? 'verified',
                'verified_at' => ($topicData['verification_status'] ?? 'verified') === 'verified' ? now() : null,
            ]
        );
    }

    public function ingestKnowledgeItem(string $topicSlug, array $itemData): AiKnowledgeItem
    {
        $topic = AiKnowledgeTopic::where('slug', $topicSlug)->firstOrFail();

        $sourceId = null;
        if (! empty($itemData['source'])) {
            $source = AiKnowledgeSource::firstOrCreate(
                ['title' => $itemData['source']['title']],
                [
                    'source_type' => $itemData['source']['type'] ?? 'reputable_industry',
                    'url' => $itemData['source']['url'] ?? null,
                    'authority_score' => $itemData['source']['authority_score'] ?? 0.90,
                    'last_fetched_at' => now(),
                ]
            );
            $sourceId = $source->id;
        }

        $existing = AiKnowledgeItem::where('topic_id', $topic->id)
            ->where('title', $itemData['title'])
            ->first();

        $version = $existing ? $existing->version + 1 : 1;

        return AiKnowledgeItem::updateOrCreate(
            [
                'topic_id' => $topic->id,
                'title' => $itemData['title'],
            ],
            [
                'source_id' => $sourceId ?? $existing?->source_id,
                'memory_type' => $itemData['memory_type'] ?? 'general_ecommerce',
                'concept' => $itemData['concept'],
                'rules' => $itemData['rules'] ?? [],
                'workflows' => $itemData['workflows'] ?? [],
                'implementation_patterns' => $itemData['implementation_patterns'] ?? null,
                'examples' => $itemData['examples'] ?? null,
                'common_problems' => $itemData['common_problems'] ?? null,
                'source_citation' => $itemData['source_citation'] ?? ($itemData['source']['title'] ?? null),
                'confidence' => $itemData['confidence'] ?? 0.95,
                'version' => $version,
                'is_active' => true,
            ]
        );
    }

    public function seedFoundationalCurriculum(): array
    {
        $curriculum = AiCurriculumRegistry::getMasterCurriculum();

        $domainsCount = 0;
        $topicsCount = 0;
        $itemsCount = 0;
        $sourcesCount = 0;

        foreach ($curriculum as $domainData) {
            $domain = $this->ingestDomain([
                'code' => $domainData['code'],
                'name' => $domainData['name'],
                'description' => $domainData['description'],
                'priority' => $domainData['priority'],
            ]);
            $domainsCount++;

            foreach ($domainData['topics'] as $topicData) {
                $topic = $this->ingestTopic($domain->code, [
                    'slug' => $topicData['slug'],
                    'name' => $topicData['name'],
                    'level' => $topicData['level'],
                    'description' => $topicData['description'],
                    'verification_status' => 'verified',
                ]);
                $topicsCount++;

                $source = null;
                if (! empty($topicData['source'])) {
                    $source = AiKnowledgeSource::updateOrCreate(
                        ['title' => $topicData['source']['title']],
                        [
                            'source_type' => $topicData['source']['type'],
                            'url' => $topicData['source']['url'],
                            'authority_score' => $topicData['source']['authority_score'],
                            'last_fetched_at' => now(),
                            'content_hash' => md5($topicData['source']['title'].$topicData['source']['url']),
                        ]
                    );
                    $sourcesCount++;
                }

                foreach ($topicData['items'] as $itemData) {
                    $itemData['source_id'] = $source?->id;
                    $itemData['source_citation'] = $source?->title;
                    $this->ingestKnowledgeItem($topic->slug, $itemData);
                    $itemsCount++;
                }
            }
        }

        return [
            'domains' => $domainsCount,
            'topics' => $topicsCount,
            'items' => $itemsCount,
            'sources' => $sourcesCount,
        ];
    }

    public function seedDefaultCurriculum(): array
    {
        return $this->seedFoundationalCurriculum();
    }
}
