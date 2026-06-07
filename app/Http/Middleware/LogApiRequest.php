<?php

namespace App\Http\Middleware;

use App\Services\ApiRequestLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    public function __construct(
        protected ApiRequestLogger $logger,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->logger->shouldLog($request)) {
            return $next($request);
        }

        $response = $next($request);

        $this->logger->log($request, $response);

        return $response;
    }
}
