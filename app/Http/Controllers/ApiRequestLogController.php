<?php

namespace App\Http\Controllers;

use App\Models\ApiRequestLog;
use Illuminate\View\View;

class ApiRequestLogController extends Controller
{
    /**
     * Display a listing of the API request logs.
     */
    public function index(): View
    {
        $logs = ApiRequestLog::query()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('api_request_logs.index', compact('logs'));
    }

    /**
     * Display the specified API request log.
     */
    public function show(ApiRequestLog $apiRequestLog): View
    {
        return view('api_request_logs.show', ['log' => $apiRequestLog]);
    }
}
