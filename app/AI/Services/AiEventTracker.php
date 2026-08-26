<?php

namespace App\AI\Services;

use App\AI\Contracts\AiEventTrackerInterface;
use App\AI\Models\AiEvent;
use Illuminate\Support\Collection;
use Throwable;

class AiEventTracker implements AiEventTrackerInterface
{
    public function track(
        string $eventType,
        ?string $entityType = null,
        string|int|null $entityId = null,
        array $metadata = []
    ): ?AiEvent {
        try {
            $userId = auth()->check() ? auth()->id() : null;
            $sessionId = session()->isStarted() ? session()->getId() : 'cli-session';

            $sanitizedMetadata = $this->sanitizeMetadata($metadata);

            return AiEvent::create([
                'event_type' => $eventType,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'entity_type' => $entityType,
                'entity_id' => $entityId !== null ? (string) $entityId : null,
                'metadata' => $sanitizedMetadata !== [] ? $sanitizedMetadata : null,
            ]);
        } catch (Throwable $e) {
            // Swallowed cleanly so user actions are never interrupted by analytics/tracking
            logger()->error('AI Event Tracking Exception: '.$e->getMessage(), [
                'event_type' => $eventType,
            ]);

            return null;
        }
    }

    public function getRecentEvents(int $limit = 50, ?string $eventType = null): Collection
    {
        $query = AiEvent::query()->latest();

        if ($eventType !== null && $eventType !== '') {
            $query->where('event_type', $eventType);
        }

        return $query->take($limit)->get();
    }

    public function getEventCounts(): array
    {
        return AiEvent::query()
            ->selectRaw('event_type, count(*) as total')
            ->groupBy('event_type')
            ->pluck('total', 'event_type')
            ->toArray();
    }

    /**
     * Remove any potential sensitive fields from event metadata.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function sanitizeMetadata(array $metadata): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            'card_number',
            'cvv',
            'secret',
            'api_key',
            'auth_token',
        ];

        return array_filter($metadata, function ($key) use ($sensitiveKeys): bool {
            return ! in_array(strtolower((string) $key), $sensitiveKeys, true);
        }, ARRAY_FILTER_USE_KEY);
    }
}
