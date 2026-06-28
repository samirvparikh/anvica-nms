<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Role extends Model
{
    public const SLUG_SUPERADMIN = 'superadmin';

    public const SLUG_ADMIN = 'admin';

    public const SLUG_MANAGER = 'manager';

    public const SLUG_ENGINEER = 'engineer';

    public const STATUS_ACTIVE = 'Active';

    protected $fillable = [
        'name',
        'slug',
        'is_superadmin',
        'is_admin',
        'is_staff',
        'assignable_by',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_superadmin' => 'boolean',
            'is_admin' => 'boolean',
            'is_staff' => 'boolean',
            'assignable_by' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::query()->where('slug', $slug)->first();
    }

    public function grantsAdminAccess(): bool
    {
        return $this->is_superadmin || $this->is_admin;
    }

    /** @return Collection<int, self> */
    public static function assignableForCreator(User $creator): Collection
    {
        $creator->loadMissing('assignedRole');

        $creatorSlug = $creator->assignedRole?->slug;
        if (! $creatorSlug) {
            return collect();
        }

        return static::query()
            ->where('status', self::STATUS_ACTIVE)
            ->whereJsonContains('assignable_by', $creatorSlug)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, self> */
    public static function assignableForEditor(User $creator, User $target): Collection
    {
        $target->loadMissing('assignedRole');

        if ($target->assignedRole?->is_superadmin) {
            return static::query()
                ->where('slug', self::SLUG_SUPERADMIN)
                ->get();
        }

        if ($creator->isAdmin() && ! $creator->isSuperAdmin() && $target->isAdmin()) {
            return $target->assignedRole
                ? collect([$target->assignedRole])
                : collect();
        }

        return static::assignableForCreator($creator);
    }

    /** @return list<int> */
    public static function allowedIdsForCreator(User $creator, ?User $target = null): array
    {
        $roles = $target
            ? static::assignableForEditor($creator, $target)
            : static::assignableForCreator($creator);

        return $roles->pluck('id')->all();
    }
}
