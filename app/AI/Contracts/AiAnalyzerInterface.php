<?php

namespace App\AI\Contracts;

interface AiAnalyzerInterface
{
    /**
     * Analyze structured user/session context to detect behavioral patterns & intent.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function analyze(array $context): array;

    /**
     * Analyze global platform activity to identify winning products, trending categories & conversion signals.
     *
     * @return array<string, mixed>
     */
    public function analyzeGlobal(int $days = 30): array;
}
