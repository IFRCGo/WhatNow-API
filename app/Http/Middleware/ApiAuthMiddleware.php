<?php

namespace App\Http\Middleware;

use App\Models\Application;
use App\Models\UsageLog;
use Carbon\Carbon;
use Closure;

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
        $apiKey = $request->header('x-api-key');
        $authHeader = $request->header('Authorization');
        $isBasicAuth = $authHeader && str_starts_with($authHeader, 'Basic ');

        if (!$apiKey  && !$isBasicAuth) {
            return response()->json(['error' => 'Authentication required. Provide API key or Basic auth'], 401);
        }

        if ($isBasicAuth) {
            return parent::handle($request, $next);
        }

        if ($apiKey) {
            $application = Application::where('key', '=', $apiKey)->first();

            if (!$application) {
                return response()->json(['error' => 'Invalid API key'], 401);
            }

            if (!$application->is_active) {
                return response()->json(['error' => 'Application is inactive'], 403);
            }
            $usageLog = new UsageLog;
            $usageLog->application_id = $application->id;
            $usageLog->method = $request->method();
            $usageLog->endpoint = $request->path();
            $usageLog->timestamp = Carbon::now()->toDateTimeString();
            $usageLog->code_status = 200;
            $usageLog->language = $request->input('language', false) ? $request->input('language', null) : $request->header('Accept-Language', null);
            $usageLog->subnational = $request->input('subnational', null);
            $usageLog->event_type = $request->input('eventType', null);
            $usageLog->save();
            $request->usageLog = $usageLog;
        }

        return $next($request);
    }
}
