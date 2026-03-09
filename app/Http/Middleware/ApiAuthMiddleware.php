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

        if (!$apiKey && !$isBasicAuth) {
            return response()->json(['error' => 'Authentication required. Provide API key or Basic auth'], 401);
        }

        if ($isBasicAuth) {
            return parent::handle($request, $next);
        }

        if ($apiKey) {
            $application = Application::query()->where('key', '=', $apiKey)->first();

            if (!$application) {

                return response()->json(['error' => 'Invalid API key'], 401);
            }


            if ($application->trashed() || !$application->is_active) {
                return response()->json(['error' => 'Application is unavailable'], 403);
            }

            $canAccess = $this->canAccessRequestedVersion($request->path(), (array) $application->rules);

            if (!$canAccess) {
                return response()->json(['error' => 'Application is not allowed to access this API version'], 403);
            }

            $canAccessOrganisation = $this->canAccessOrganisation($request->path(), (array) $application->rules);

            if (!$canAccessOrganisation) {
                return response()->json(['error' => 'Application is not allowed to access this organisation'], 403);
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
            return $rules['can_access_legacy_whatnow'];
        }

        if (strpos($path, 'v2/') === 0) {
            return $rules['can_access_preparedness_v2'];
        }

        return true;
    }

    private function canAccessOrganisation(string $path, array $rules): bool
    {
        // Check if accessing org/{code}/whatnow endpoint
        if (preg_match('#org/([^/]+)/whatnow#', $path, $matches)) {
            $orgCode = $matches[1];

            // If allowed_country_code is defined in rules, check if this org is restricted
            if (isset($rules['allowed_country_code']) && is_array($rules['allowed_country_code'])) {
                if (!in_array($orgCode, $rules['allowed_country_code'])) {
                    return false;
                }
            }
        }

        return true;
    }
}
