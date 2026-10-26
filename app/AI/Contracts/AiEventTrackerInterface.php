<?php

namespace App\AI\Contracts;

use App\AI\Models\AiEvent;
use Illuminate\Support\Collection;

interface AiEventTrackerInterface
{
    /**
     * Track a user event in the AI event engine.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function track(
        string $eventType,
        ?string $entityType = null,
        string|int|null $entityId = null,
        array $metadata = []
    ): ?AiEvent;

    /**
     * Get recent recorded AI events.
     *
     * @return Collection<int, AiEvent>
     */
    public function getRecentEvents(int $limit = 50, ?string $eventType = null): Collection;

    /**
     * Get counts grouped by event type.
     *
     * @return array<string, int>
     */
    public function getEventCounts(): array;
}
