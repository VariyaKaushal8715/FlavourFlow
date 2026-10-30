<?php

namespace App\AI\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $source_type
 * @property string|null $url
 * @property float $authority_score
 * @property string|null $content_hash
 * @property string|null $notes
 * @property Collection<int, AiKnowledgeItem> $items
 */
class AiKnowledgeSource extends Model
{
    protected $table = 'ai_knowledge_sources';

    protected $fillable = [
        'title',
        'source_type',
        'url',
        'authority_score',
        'last_fetched_at',
        'content_hash',
        'notes',
    ];

    protected $casts = [
        'authority_score' => 'float',
        'last_fetched_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(AiKnowledgeItem::class, 'source_id');
    }
}

