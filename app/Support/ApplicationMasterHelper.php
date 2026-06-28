<?php

namespace App\Support;

use App\Models\ApplicationMaster;
use App\Services\ApplicationMasterService;

class ApplicationMasterHelper
{
    public static function service(): ApplicationMasterService
    {
        return app(ApplicationMasterService::class);
    }

    /**
     * @return array<int, string> id => display label
     */
    public static function options(string $type, bool $includeEmpty = true, string $emptyLabel = 'Select'): array
    {
        return self::service()->optionsByIdForSelect($type, $includeEmpty, $emptyLabel);
    }

    public static function label(?int $id, ?string $fallback = null): ?string
    {
        return self::service()->label($id, $fallback);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function labelsForModel(string $modelKey, object $model, ?array $attributes = null): array
    {
        return self::service()->labelsForModel($modelKey, $model, $attributes);
    }

    public static function resolveId(string $type, mixed $value): ?int
    {
        return self::service()->resolveId($type, $value);
    }

    public static function fieldMap(string $modelKey = 'assets'): array
    {
        return config("application_master_fields.{$modelKey}", []);
    }

    public static function masterTypeForColumn(string $column, string $modelKey = 'assets'): ?string
    {
        return self::fieldMap($modelKey)[$column] ?? null;
    }
}
