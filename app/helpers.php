<?php

use App\Models\ApplicationMaster;
use App\Support\ApplicationMasterHelper;
use Illuminate\Support\Facades\Schema;

if (! function_exists('master_options')) {
    /**
     * @return array<int, string>
     */
    function master_options(string $type, bool $includeEmpty = true, string $emptyLabel = 'Select'): array
    {
        return ApplicationMasterHelper::options($type, $includeEmpty, $emptyLabel);
    }
}

if (! function_exists('master_label')) {
    function master_label(?int $id, ?string $fallback = null): ?string
    {
        return ApplicationMasterHelper::label($id, $fallback);
    }
}

if (! function_exists('master_type_for_column')) {
    function master_type_for_column(string $column, string $modelKey = 'assets'): ?string
    {
        return ApplicationMasterHelper::masterTypeForColumn($column, $modelKey);
    }
}
