<?php

namespace App\Http\Controllers;

use App\Models\ApiRequestLog;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Handle incoming test request and log details to database.
     */
    public function handleTestRequest(Request $Request)
    {
        // Extract caller metadata
        $logData = [
            'url' => $Request->fullUrl(),
            'method' => $Request->method(),
            'ip_address' => $Request->ip(),
            'user_agent' => $Request->userAgent(),
            'referer' => $Request->header('referer') ?? $Request->header('referrer') ?? $Request->server('HTTP_REFERER'),
            'request_data' => $Request->all(),
            'headers' => collect($Request->headers->all())->map(function ($item) {
                // Headers are arrays of strings in Symfony request, let's simplify for JSON reading
                return is_array($item) && count($item) === 1 ? $item[0] : $item;
            })->toArray(),
        ];

        // Store log in database
        $log = ApiRequestLog::create($logData);

        // Return a response confirming the log insertion
        return response()->json([
            'status' => 'success',
            'message' => 'API request successfully logged to MySQL database.',
            'logged_data' => [
                'id' => $log->id,
                'url' => $log->url,
                'method' => $log->method,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'referer' => $log->referer,
                'request_data' => $log->request_data,
                'timestamp' => $log->created_at->toIso8601String(),
            ]
        ], 200);
    }
}
