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
use Illuminate\Support\Collection;

#[Fillable([
    'name', 'email', 'username', 'mobile', 'password', 'role_id', 'is_admin', 'status', 'device_limit', 'start_date', 'expire_date', 'created_by',
    'employee_id', 'alternate_number', 'alternate_email', 'dob', 'gender', 'language', 'profile_photo', 'signature',
    'department', 'designation', 'reporting_manager', 'office_location', 'work_location', 'timezone', 'address', 'landline', 'extension',
    'auth_type', 'failed_login_attempts', 'lockout_minutes', 'two_factor', 'force_password_change', 'password_expiry_days',
    'skills', 'certifications', 'notes', 'assigned_roles', 'module_access', 'sla_policy_id',
    'business_unit', 'service_categories', 'max_tickets_per_day', 'max_changes_per_week',
    'notification_methods', 'alert_emails', 'working_hours', 'escalation_group', 'preferred_dashboard',
    'id_proof', 'offer_letter', 'other_document'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = Role::SLUG_SUPERADMIN;

    public const ROLE_ADMIN = Role::SLUG_ADMIN;

    public const ROLE_MANAGER = Role::SLUG_MANAGER;

    public const ROLE_ENGINEER = Role::SLUG_ENGINEER;

    /** @deprecated Legacy slug mapped to engineer */
    public const ROLE_USER = 'user';

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    public function roleLabel(): string
    {
        return $this->assignedRole?->name ?? 'User';
    }

    public function roleSlug(): string
    {
        return $this->assignedRole?->slug ?? self::ROLE_ENGINEER;
    }

    public function normalizedRoleSlug(): string
    {
        $slug = $this->roleSlug();

        return $slug === self::ROLE_USER ? self::ROLE_ENGINEER : $slug;
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->assignedRole?->is_superadmin;
    }

    public function isStaff(): bool
    {
        return (bool) $this->assignedRole?->is_staff;
    }

    /** @return Collection<int, Role> */
    public static function assignableRolesForCreator(User $creator): Collection
    {
        return Role::assignableForCreator($creator);
    }

    /** @return Collection<int, Role> */
    public static function assignableRolesForEditor(User $creator, User $target): Collection
    {
        return Role::assignableForEditor($creator, $target);
    }

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
            'is_admin' => 'boolean',
            'start_date' => 'date',
            'expire_date' => 'date',
            'dob' => 'date',
            'two_factor' => 'boolean',
            'force_password_change' => 'boolean',
            'assigned_roles' => 'array',
            'module_access' => 'array',
            'service_categories' => 'array',
            'notification_methods' => 'array',
            'alert_emails' => 'array',
            'skills' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        if ($this->assignedRole?->grantsAdminAccess()) {
            return true;
        }

        return (bool) $this->is_admin && ! $this->isStaff();
    }

    public function isActive(): bool
    {
        if ($this->status === self::STATUS_INACTIVE) {
            return false;
        }

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

    /** @param  \Illuminate\Database\Eloquent\Builder<User>  $query */
    public function scopeCustomers($query)
    {
        return $query->where('is_admin', false)
            ->whereHas('assignedRole', fn ($roleQuery) => $roleQuery->where('is_staff', true));
    }

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'customer_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }
}
