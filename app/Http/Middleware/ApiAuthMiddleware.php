<?php

namespace App\Http\Middleware;

use App\Models\Application;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Log;

class ApiAuthMiddleware extends BasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $application = Application::where('key', '=', $request->header('x-api-key'))->first();

        if (! $application) {
            return parent::handle($request, $next);
        }

        // Configure payload for logging API usage data
        $event = [
            'type' => 'APIRequestEvent',
            'payload' => [
                'app_id' => $application->id,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'timestamp' => Carbon::now()->toDateTimeString(),
            ],
        ];

        // Log request to CloudWatch
        Log::channel('cloudwatch_access')->info(json_encode($event));

        return $next($request);
    }
}
