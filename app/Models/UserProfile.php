<?php

namespace App\Models;

use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'mobile_number',
        'email',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'pincode',
    ];

    public function setPincodeAttribute($value): void
    {
        $this->attributes['postal_code'] = $value;
    }

    public function getPincodeAttribute(): ?string
    {
        return $this->attributes['postal_code'] ?? null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
