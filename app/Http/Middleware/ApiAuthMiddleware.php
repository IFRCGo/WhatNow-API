<?php

namespace App\Http\Middleware;

use App\Models\Application;
use App\Models\UsageLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Log;

class ApiAuthMiddleware extends BasicAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $apiKey = $request->header('x-api-key');
        $authHeader = $request->header('Authorization');
        $isBasicAuth = $authHeader && str_starts_with($authHeader, 'Basic ');

        Log::debug('ApiAuthMiddleware: incoming request', [
            'path' => $request->path(),
            'method' => $request->method(),
            'has_api_key' => !empty($apiKey),
            'is_basic_auth' => (bool) $isBasicAuth,
        ]);

        if (!$apiKey && !$isBasicAuth) {
            Log::debug('ApiAuthMiddleware: authentication missing');
            return response()->json(['error' => 'Authentication required. Provide API key or Basic auth'], 401);
        }

        if ($isBasicAuth) {
            Log::debug('ApiAuthMiddleware: delegating to BasicAuth');
            return parent::handle($request, $next);
        }

        if ($apiKey) {
            $application = Application::query()->where('key', '=', $apiKey)->first();

            if (!$application) {
                Log::debug('ApiAuthMiddleware: invalid api key', [
                    'api_key_prefix' => substr($apiKey, 0, 6),
                    'path' => $request->path(),
                ]);
                return response()->json(['error' => 'Invalid API key'], 401);
            }

            Log::debug('ApiAuthMiddleware: application resolved', [
                'application_id' => $application->id,
                'tenant_user_id' => $application->tenant_user_id,
                'is_active' => (bool) $application->is_active,
                'is_trashed' => $application->trashed(),
                'rules' => (array) $application->rules,
            ]);

            if ($application->trashed() || !$application->is_active) {
                Log::debug('ApiAuthMiddleware: application unavailable', [
                    'application_id' => $application->id,
                ]);
                return response()->json(['error' => 'Application is unavailable'], 403);
            }

            $canAccess = $this->canAccessRequestedVersion($request->path(), (array) $application->rules);

            Log::debug('ApiAuthMiddleware: version access decision', [
                'application_id' => $application->id,
                'path' => $request->path(),
                'can_access' => $canAccess,
            ]);

            if (!$canAccess) {
                return response()->json(['error' => 'Application is not allowed to access this API version'], 403);
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

    private function canAccessRequestedVersion(string $path, array $rules): bool
    {
        if (strpos($path, 'v1/') === 0) {
            return !empty($rules['can_access_legacy_whatnow']);
        }

        if (strpos($path, 'v2/') === 0) {
            return !empty($rules['can_access_preparedness_v2']);
        }

        return true;
    }
}
