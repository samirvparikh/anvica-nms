<?php

namespace App\Services;

use App\Models\ApplicationMaster;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ApplicationMasterService
{
    /**
     * @return Collection<int, ApplicationMaster>
     */
    public function activeByType(string $type): Collection
    {
        return ApplicationMaster::query()
            ->where('type', $type)
            ->where('status', ApplicationMaster::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, string> id => display label
     */
    public function optionsByIdForSelect(string $type, bool $includeEmpty = true, string $emptyLabel = 'Select'): array
    {
        $options = [];

        if ($includeEmpty) {
            $options[''] = $emptyLabel;
        }

        foreach ($this->activeByType($type) as $item) {
            $options[$item->id] = $item->name;
        }

        return $options;
    }

    /**
     * @return array<string, string> value => name (legacy)
     */
    public function optionsForSelect(string $type, bool $includeEmpty = true, string $emptyLabel = 'Select'): array
    {
        $options = [];

        if ($includeEmpty) {
            $options[''] = $emptyLabel;
        }

        foreach ($this->activeByType($type) as $item) {
            $options[$item->value] = $item->name;
        }

        return $options;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function optionsForAssetForm(): array
    {
        $options = [];

        foreach (config('application_master_fields.assets', []) as $column => $type) {
            $options[$column] = $this->optionsByIdForSelect($type);
        }

        return $options;
    }

    public function label(?int $id, ?string $fallback = null): ?string
    {
        if (! $id) {
            return $fallback;
        }

        $master = $this->findCached($id);

        return $master?->name ?? $fallback;
    }

    /**
     * @param  array<int>  $ids
     * @return array<int, string>
     */
    public function labelsByIds(array $ids): array
    {
        $ids = array_values(array_filter(array_unique($ids)));

        if ($ids === []) {
            return [];
        }

        return ApplicationMaster::query()
            ->whereIn('id', $ids)
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string|null>
     */
    public function labelsForModel(string $modelKey, object $model, ?array $attributes = null): array
    {
        $map = config("application_master_fields.{$modelKey}", []);
        $labels = [];

        foreach ($map as $column => $type) {
            $id = $attributes[$column] ?? $model->{$column} ?? null;
            $labels[$column] = $this->label(is_numeric($id) ? (int) $id : null);
        }

        return $labels;
    }

    public function resolveId(string $type, mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $exists = ApplicationMaster::query()
                ->where('id', (int) $value)
                ->where('type', $type)
                ->exists();

            return $exists ? (int) $value : null;
        }

        $normalized = $this->normalizeLookupValue($type, (string) $value);

        $master = ApplicationMaster::query()
            ->where('type', $type)
            ->where(function ($query) use ($normalized, $value) {
                $query->where('value', $normalized)
                    ->orWhere('name', $normalized)
                    ->orWhere('value', $value)
                    ->orWhere('name', $value);
            })
            ->first();

        return $master?->id;
    }

    public function validateMasterId(string $type, mixed $id): bool
    {
        if ($id === null || $id === '') {
            return true;
        }

        return ApplicationMaster::query()
            ->where('id', $id)
            ->where('type', $type)
            ->where('status', ApplicationMaster::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * @return array<int, array{type: string, label: string, count: int}>
     */
    public function typeSummaries(): array
    {
        $counts = ApplicationMaster::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $summaries = [];

        foreach (ApplicationMaster::sortedTypeLabels() as $type => $label) {
            $summaries[] = [
                'type' => $type,
                'label' => $label,
                'count' => (int) ($counts[$type] ?? 0),
            ];
        }

        return $summaries;
    }

    protected function findCached(int $id): ?ApplicationMaster
    {
        return Cache::remember("application_master.{$id}", 300, function () use ($id) {
            return ApplicationMaster::find($id);
        });
    }

    protected function normalizeLookupValue(string $type, string $value): string
    {
        if ($type === 'snmp_version') {
            return match (strtolower($value)) {
                '1', 'v1' => 'v1',
                '2', '2c', 'v2', 'v2c' => 'v2c',
                '3', 'v3' => 'v3',
                default => $value,
            };
        }

        if ($type === 'asset_status') {
            return match (strtolower($value)) {
                'active' => 'Active',
                'inactive' => 'Inactive',
                'maintenance' => 'Maintenance',
                default => $value,
            };
        }

        return $value;
    }
}
