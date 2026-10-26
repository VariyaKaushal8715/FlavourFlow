<?php

namespace App\AI\Models;

use App\Models\User;
use Database\Factories\AiEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEvent extends Model
{
    use HasFactory, Prunable;

    protected $table = 'ai_events';

    protected $fillable = [
        'event_type',
        'user_id',
        'session_id',
        'entity_type',
        'entity_id',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<=', now()->subDays(90));
    }

    protected static function newFactory(): AiEventFactory
    {
        return AiEventFactory::new();
    }
}
