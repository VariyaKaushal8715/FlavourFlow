<?php

namespace App\AI\Contracts;

use App\AI\Models\AiKnowledgeGap;

interface AiKnowledgeBaseInterface
{
    /**
     * Search knowledge base using multi-field lexical, keyword and domain matching.
     *
     * @param  array{domain?: ?string, memory_type?: ?string, level?: ?string, limit?: int}  $options
     * @return array{items: list<array<string, mixed>>, total: int, domain_matched: ?string}
     */
    public function search(string $query, array $options = []): array;

    /**
     * Retrieve knowledge items belonging to a specific domain.
     *
     * @return list<array<string, mixed>>
     */
    public function retrieveByDomain(string $domainCode, int $limit = 10): array;

    /**
     * Retrieve items by memory type (e.g. general_ecommerce, store_business, technical_architecture).
     *
     * @return list<array<string, mixed>>
     */
    public function retrieveByMemoryType(string $memoryType, int $limit = 10): array;

    /**
     * Record a knowledge gap when a domain query has missing or low-confidence info.
     */
    public function recordKnowledgeGap(string $query, ?string $domainCode = null, string $reason = ''): AiKnowledgeGap;

    /**
     * Get aggregate knowledge base statistics.
     *
     * @return array{total_domains: int, total_topics: int, verified_topics: int, total_items: int, total_sources: int, pending_gaps: int}
     */
    public function getStats(): array;
}

