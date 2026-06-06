<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'mobile', 'password', 'role', 'device_limit', 'start_date', 'expire_date', 'created_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'start_date' => 'date',
            'expire_date' => 'date',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isActive(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->expire_date && $this->expire_date->isPast()) {
            return false;
        }

        return true;
    }

    public function deviceCount(): int
    {
        return $this->devices()->count();
    }

    public function canAddDevice(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isActive()) {
            return false;
        }

        if ($this->device_limit === null) {
            return true;
        }

        return $this->deviceCount() < $this->device_limit;
    }

    public function remainingDeviceSlots(): ?int
    {
        if ($this->isAdmin() || $this->device_limit === null) {
            return null;
        }

        return max(0, $this->device_limit - $this->deviceCount());
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }
}
