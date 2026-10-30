<?php

namespace App\AI\Contracts;

use App\AI\Models\AiKnowledgeDomain;
use App\AI\Models\AiKnowledgeItem;
use App\AI\Models\AiKnowledgeTopic;

interface AiKnowledgeIngestionInterface
{
    /**
     * Ingest or update a knowledge domain.
     *
     * @param  array{code: string, name: string, description: string, priority?: int, metadata?: array<string, mixed>}  $domainData
     */
    public function ingestDomain(array $domainData): AiKnowledgeDomain;

    /**
     * Ingest or update a topic within a domain.
     *
     * @param  array{slug: string, name: string, description?: ?string, level?: string, parent_slug?: ?string, verification_status?: string}  $topicData
     */
    public function ingestTopic(string $domainCode, array $topicData): AiKnowledgeTopic;

    /**
     * Ingest or update a knowledge item under a topic.
     *
     * @param  array{title: string, concept: string, rules?: array<int, string>, workflows?: array<int, string>, implementation_patterns?: ?string, examples?: ?string, common_problems?: ?string, memory_type?: string, source_citation?: ?string, confidence?: float}  $itemData
     */
    public function ingestKnowledgeItem(string $topicSlug, array $itemData): AiKnowledgeItem;

    /**
     * Seed or sync the foundational 18-domain e-commerce curriculum.
     *
     * @return array{domains: int, topics: int, items: int, sources: int}
     */
    public function seedFoundationalCurriculum(): array;

    /**
     * Alias for seedFoundationalCurriculum.
     *
     * @return array{domains: int, topics: int, items: int, sources: int}
     */
    public function seedDefaultCurriculum(): array;
}
