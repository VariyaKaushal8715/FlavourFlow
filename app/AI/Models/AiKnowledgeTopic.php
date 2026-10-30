<?php

namespace App\AI\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $domain_id
 * @property int|null $parent_id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property string $level
 * @property string $verification_status
 * @property Carbon|null $verified_at
 * @property AiKnowledgeDomain $domain
 * @property Collection<int, AiKnowledgeTopic> $children
 * @property Collection<int, AiKnowledgeItem> $items
 */
class AiKnowledgeTopic extends Model
{
    protected $table = 'ai_knowledge_topics';

    protected $fillable = [
        'domain_id',
        'parent_id',
        'slug',
        'name',
        'description',
        'level',
        'verification_status',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(AiKnowledgeDomain::class, 'domain_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AiKnowledgeTopic::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AiKnowledgeTopic::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AiKnowledgeItem::class, 'topic_id');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }
}
