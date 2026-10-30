<?php

namespace App\AI\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $domain_id
 * @property string $query_or_topic
 * @property string $description
 * @property string $recommended_action
 * @property string $status
 * @property Carbon $identified_at
 * @property Carbon|null $resolved_at
 * @property AiKnowledgeDomain|null $domain
 */
class AiKnowledgeGap extends Model
{
    protected $table = 'ai_knowledge_gaps';

    protected $fillable = [
        'domain_id',
        'query_or_topic',
        'description',
        'recommended_action',
        'status',
        'identified_at',
        'resolved_at',
    ];

    protected $casts = [
        'identified_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (AiKnowledgeGap $gap): void {
            if ($gap->identified_at === null) {
                $gap->identified_at = now();
            }
            if (empty($gap->recommended_action)) {
                $gap->recommended_action = 'Ingest official documentation on this topic.';
            }
        });
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(AiKnowledgeDomain::class, 'domain_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['identified', 'learning_in_progress']);
    }
}
