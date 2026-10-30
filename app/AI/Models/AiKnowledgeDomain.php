<?php

namespace App\AI\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property int $priority
 * @property bool $is_active
 * @property array<string, mixed>|null $metadata
 * @property Collection<int, AiKnowledgeTopic> $topics
 * @property Collection<int, AiKnowledgeItem> $items
 */
class AiKnowledgeDomain extends Model
{
    protected $table = 'ai_knowledge_domains';

    protected $fillable = [
        'code',
        'name',
        'description',
        'priority',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'metadata' => 'array',
    ];

    public function topics(): HasMany
    {
        return $this->hasMany(AiKnowledgeTopic::class, 'domain_id');
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(AiKnowledgeItem::class, AiKnowledgeTopic::class, 'domain_id', 'topic_id');
    }

    public function gaps(): HasMany
    {
        return $this->hasMany(AiKnowledgeGap::class, 'domain_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

