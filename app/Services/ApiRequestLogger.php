<?php

namespace App\Services;

use App\Models\ApiRequestLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestLogger
{
    public function shouldLog(Request $request): bool
    {
        return $request->is('api') || $request->is('api/*');
    }

    public function log(Request $request, ?Response $response = null): ApiRequestLog
    {
        $requestData = $request->all();
        $rawContent = $request->getContent();

        if ($requestData === [] && $rawContent !== '') {
            $requestData = ['raw' => $rawContent];
        }

        return ApiRequestLog::create([
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip_address' => $request->ip() ?? '',
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer')
                ?? $request->header('referrer')
                ?? $request->server('HTTP_REFERER'),
            'request_data' => $requestData ?: null,
            'headers' => collect($request->headers->all())
                ->map(fn ($item) => is_array($item) && count($item) === 1 ? $item[0] : $item)
                ->toArray(),
            'response_status' => $response?->getStatusCode(),
            'route_exists' => $request->route() !== null,
        ]);
    }
}
