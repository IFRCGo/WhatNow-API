<?php

namespace App\Http\Middleware;

use App\Models\Application;
use App\Models\UsageLog;
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
        $request->usageLog=$usageLog;

        return $next($request);
    }
}
