<?php

namespace App\AI\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $topic_id
 * @property int|null $source_id
 * @property string $memory_type
 * @property string $title
 * @property string $concept
 * @property array<int, string>|null $rules
 * @property array<int, string>|null $workflows
 * @property string|null $implementation_patterns
 * @property string|null $examples
 * @property string|null $common_problems
 * @property string|null $source_citation
 * @property float $confidence
 * @property int $version
 * @property bool $is_active
 * @property AiKnowledgeTopic $topic
 * @property AiKnowledgeSource|null $source
 */
class AiKnowledgeItem extends Model
{
    protected $table = 'ai_knowledge_items';

    protected $fillable = [
        'topic_id',
        'source_id',
        'memory_type',
        'title',
        'concept',
        'rules',
        'workflows',
        'implementation_patterns',
        'examples',
        'common_problems',
        'source_citation',
        'confidence',
        'version',
        'is_active',
    ];

    protected $casts = [
        'rules' => 'array',
        'workflows' => 'array',
        'confidence' => 'float',
        'version' => 'integer',
        'is_active' => 'boolean',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AiKnowledgeTopic::class, 'topic_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(AiKnowledgeSource::class, 'source_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMemoryType(Builder $query, string $type): Builder
    {
        return $query->where('memory_type', $type);
    }
}

