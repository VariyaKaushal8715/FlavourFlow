<?php

namespace App\Models;

use Database\Factories\AdminProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AdminProfile extends Model
{
    /** @use HasFactory<AdminProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_photo_path',
        'full_name',
        'mobile_number',
        'email',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'date_of_birth',
        'gender',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profilePhotoUrl(): ?string
    {
        if (blank($this->profile_photo_path)) {
            return null;
        }

        if (Str::startsWith($this->profile_photo_path, ['http://', 'https://', '/'])) {
            return $this->profile_photo_path;
        }

        return asset($this->profile_photo_path);
    }

    public function uploadedPhotoStoragePath(): ?string
    {
        if (blank($this->profile_photo_path)) {
            return null;
        }

        if (Str::startsWith($this->profile_photo_path, 'storage/')) {
            return Str::after($this->profile_photo_path, 'storage/');
        }

        return null;
    }
}
