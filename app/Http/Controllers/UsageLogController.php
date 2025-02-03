<?php

namespace App\Http\Controllers;

use App\Classes\Repositories\ApplicationRepositoryInterface;
use App\Classes\Repositories\UsageLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use App\Classes\CustomPaginator;

class UsageLogController extends Controller
{
    protected $applicationRepo;
    protected $usageLogRepo;
    protected $manager;

    public function __construct(ApplicationRepositoryInterface $applicationRepo, UsageLogRepositoryInterface $usageLogRepo, Manager $manager)
    {
        $this->applicationRepo = $applicationRepo;
        $this->manager = $manager;
        $this->usageLogRepo = $usageLogRepo;
    }

    public function getApplicationLogs(Request $request)
    {
        $this->validate($request, [
            'fromDate' => 'sometimes|date',
            'toDate' => 'sometimes|date',
        ]);

        try {
            $apps = $this->applicationRepo->allDesc(['id', 'tenant_user_id', 'name', 'estimated_users_count']);

            $usageLogs = collect([]);

            foreach ($apps as $app) {
                $usageLogs->push([
                    'id' => $app->id,
                    'tenant_user_id' => $app->tenant_user_id,
                    'name' => $app->name,
                    'estimatedUsers' => $app->estimated_users_count,
                    'requestCount' => count($this->usageLogRepo->getForApplication($app->id, $request->fromDate, $request->toDate)),
                ]);
            }

            $paginated = new CustomPaginator(
                $usageLogs->forPage($request->page, 10),
                $usageLogs->count(),
                10,
                $request->page
            );

            return response()->json([
                'data' => $paginated->toArray(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Could not get Usage Log', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Usage Log',
                'errors' => [],
            ], 500);
        }
    }

    public function getEndpointLogs(Request $request)
    {
        $this->validate($request, [
            'fromDate' => 'sometimes|date',
            'toDate' => 'sometimes|date',
        ]);

        try {
            $usageLogs = $this->usageLogRepo->listByEndpoint($request->fromDate, $request->toDate);

            $usageLogs->transform(function ($usageLog) {
                $application = $this->applicationRepo->find($usageLog->application_id);

                $usageLog->application_name = $application['name'] ?? 'Unknown';
                $usageLog->application_tenant_user_id = $application['tenant_user_id'] ?? null;

                return $usageLog;
            });

            $paginated = new CustomPaginator(
                $usageLogs->forPage($request->page, 10),
                $usageLogs->count(),
                10,
                $request->page
            );

            return response()->json([
                'data' => $paginated->toArray(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Could not get Usage Log', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Usage Log',
                'errors' => [],
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $this->validate($request, [
            'fromDate' => 'sometimes|date',
            'toDate' => 'sometimes|date',
        ]);

        try {
            $usageLogs = $this->usageLogRepo->whereBetween($request->fromDate, $request->toDate, ['*']);

            $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());

            $csv->insertOne([
                'Application Name',
                'Endpoint',
                'Method',
                'Timestamp',
            ]);

            $applications = $this->applicationRepo->all();

            foreach ($usageLogs as $usageLog) {
                $application = $applications->where('id', $usageLog->application_id)->first();

                $csv->insertOne([
                    $application ? $application->name : 'Unknown',
                    $usageLog->endpoint,
                    $usageLog->method,
                    $usageLog->timestamp,
                ]);
            }

            print_r($csv->toString());

        } catch (\Exception $e) {
            Log::error('Could not export Usage Logs', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not export Usage Logs',
                'errors' => [],
            ], 500);
        }
    }

    public function getForApplication(int $applicationId)
    {
        try {
            $usageLogs = Cache::remember("usage.application.{$applicationId}", 3600, function () use ($applicationId) {
                return $this->usageLogRepo->getForApplication($applicationId);
            });

            return response()->json([
                'count' => count($usageLogs),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Could not get Usage Logs for application', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Usage Logs for application',
                'errors' => [],
            ], 500);
        }
    }

    public function getTotals()
    {
        try {
            $totals = Cache::remember('usage.totals', 3600 * 24, function () {
                $applications = $this->applicationRepo->all();
                $usageLogs = $this->usageLogRepo->all();

                $totalEstimatedUsers = $applications->map(function ($application) {
                    return $application->estimated_users_count;
                })->sum();

                return [
                    'applications' => count($applications),
                    'estimatedUsers' => $totalEstimatedUsers,
                    'hits' => count($usageLogs),
                ];
            });

            return response()->json([
                'data' => $totals,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Could not get Usage Log totals', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Usage Log totals',
                'errors' => [],
            ], 500);
        }
    }
}
