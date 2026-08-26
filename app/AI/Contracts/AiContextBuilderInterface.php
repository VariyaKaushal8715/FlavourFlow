<?php

namespace App\AI\Contracts;

interface AiContextBuilderInterface
{
    /**
     * Build structured AI context for a specific user or session from raw events.
     *
     * @return array<string, mixed>
     */
    public function buildContext(?int $userId = null, ?string $sessionId = null, int $limit = 100): array;

    /**
     * Build aggregated global context across all user activity.
     *
     * @return array<string, mixed>
     */
    public function buildGlobalContext(int $days = 30): array;
}
