<?php

namespace App\AI\Contracts;

interface AiConsultantInterface
{
    /**
     * Provide comprehensive e-commerce domain consulting, architectural guidance, or business insight.
     *
     * @param  array<string, mixed>  $context
     * @param  array{language?: string, audience?: string, inspect_code?: bool, include_bi?: bool}  $options
     * @return array{
     *     domain: string,
     *     domain_title: string,
     *     role: string,
     *     answer: string,
     *     language: string,
     *     confidence: float,
     *     citations: list<string>,
     *     rules: list<string>,
     *     workflows: list<string>,
     *     implementation_patterns: ?string,
     *     bi_data: ?array<string, mixed>,
     *     code_inspection: ?array<string, mixed>,
     *     actionable_recommendations: list<array<string, mixed>>,
     *     is_gap: bool,
     *     gap_notes: ?string
     * }
     */
    public function consult(string $query, array $context = [], array $options = []): array;

    /**
     * Analyze store business intelligence metrics and generate evidence-based decisions.
     *
     * @return array{
     *     sales_summary: array<string, mixed>,
     *     inventory_health: array<string, mixed>,
     *     cart_abandonment: array<string, mixed>,
     *     customer_metrics: array<string, mixed>,
     *     strategic_recommendations: list<array<string, mixed>>
     * }
     */
    public function analyzeBusinessMetrics(int $days = 30): array;

    /**
     * Diagnose store health including sales, inventory, funnel conversion, and strategic risks.
     *
     * @return array<string, mixed>
     */
    public function diagnoseStore(int $days = 30): array;
}
