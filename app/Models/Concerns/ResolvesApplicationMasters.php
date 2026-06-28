<?php

namespace App\Models\Concerns;

use App\Models\ApplicationMaster;
use App\Support\ApplicationMasterHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ResolvesApplicationMasters
{
    public function masterLabel(string $column, ?string $fallback = null): ?string
    {
        $id = $this->attributes[$column] ?? null;

        return ApplicationMasterHelper::label(is_numeric($id) ? (int) $id : null, $fallback);
    }

    /**
     * @return array<string, string|null>
     */
    public function masterLabels(?string $modelKey = null): array
    {
        $modelKey ??= $this->masterFieldModelKey();

        return ApplicationMasterHelper::service()->labelsForModel($modelKey, $this);
    }

    public function masterRelation(string $column): BelongsTo
    {
        return $this->belongsTo(ApplicationMaster::class, $column);
    }

    public function getAttribute($key)
    {
        $idColumn = $this->legacyKeyToIdColumn($key);

        if ($idColumn && ! array_key_exists($key, $this->attributes) && array_key_exists($idColumn, $this->attributes)) {
            return $this->masterLabel($idColumn);
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        $idColumn = $this->legacyKeyToIdColumn($key);

        if ($idColumn && $value !== null && $value !== '' && ! is_numeric($value)) {
            $type = $this->masterTypeForIdColumn($idColumn);
            $resolvedId = ApplicationMasterHelper::resolveId($type, $value);

            if ($resolvedId) {
                return parent::setAttribute($idColumn, $resolvedId);
            }
        }

        return parent::setAttribute($key, $value);
    }

    protected function masterFieldModelKey(): string
    {
        return 'assets';
    }

    protected function legacyKeyToIdColumn(string $key): ?string
    {
        $map = config('application_master_fields.'.$this->masterFieldModelKey(), []);

        if (isset($map[$key])) {
            return $key;
        }

        $candidate = $key.'_id';

        return isset($map[$candidate]) ? $candidate : null;
    }

    protected function masterTypeForIdColumn(string $idColumn): ?string
    {
        return config('application_master_fields.'.$this->masterFieldModelKey())[$idColumn] ?? null;
    }
}
