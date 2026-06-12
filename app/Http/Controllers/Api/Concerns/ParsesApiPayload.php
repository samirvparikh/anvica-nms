<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Monitoring\Normalizers\MikroTikPipeParser;
use Illuminate\Http\Request;

trait ParsesApiPayload
{
    protected function extractPayload(Request $request): array
    {
        $all = $request->all();

        if (isset($all['request_data'])) {
            if (is_array($all['request_data'])) {
                return $all['request_data'];
            }

            if (is_string($all['request_data']) && $all['request_data'] !== '') {
                $decoded = json_decode($all['request_data'], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

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
