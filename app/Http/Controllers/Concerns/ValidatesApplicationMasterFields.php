<?php

namespace App\Http\Controllers\Concerns;

trait ValidatesApplicationMasterFields
{
    /**
     * @param  array<int, string>  $requiredColumns
     * @return array<string, string>
     */
    protected function applicationMasterRules(array $requiredColumns = [], string $modelKey = 'assets'): array
    {
        $rules = [];

        foreach (config("application_master_fields.{$modelKey}", []) as $column => $type) {
            $rules[$column] = in_array($column, $requiredColumns, true)
                ? 'required|integer|exists:application_masters,id'
                : 'nullable|integer|exists:application_masters,id';
        }

        return $rules;
    }
}
