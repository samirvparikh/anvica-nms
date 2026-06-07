<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Monitoring\Normalizers\MikroTikPipeParser;
use Illuminate\Http\Request;

trait ParsesApiPayload
{
    protected function extractPayload(Request $request): array
    {
        $all = $request->all();

        if (isset($all['SYSTEM'])) {
            return $all;
        }

        if ($request->filled('data') && is_string($request->input('data'))) {
            return MikroTikPipeParser::parse($request->input('data'));
        }

        if (count($all) === 1) {
            $key = array_key_first($all);
            if (is_string($key) && str_contains($key, '_|_')) {
                return MikroTikPipeParser::parse($key);
            }
        }

        $body = trim($request->getContent());
        if ($body !== '' && str_contains($body, '_|_')) {
            return MikroTikPipeParser::parse($body);
        }

        return $all;
    }
}
