<?php

namespace App\Http\Controllers;

use App\Classes\Repositories\ApplicationRepositoryInterface;
use App\Classes\Repositories\UsageLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use App\Models\UsageLog;

/**
 * @OA\Tag(
 *     name="UsageLogs",
 *     description="Operations about UsageLog"
 * )
 */
class UsageLogController extends Controller
{
    /**
     * @var ApplicationRepositoryInterface
     */
    protected $applicationRepo;

    /**
     * @var UsageLogRepositoryInterface
     */
    protected $usageLogRepo;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Create a new controller instance.
     *
     * @param UsageLogRepositoryInterface $usageLogRepo
     * @param Manager $manager
     */
    public function __construct(ApplicationRepositoryInterface $applicationRepo, UsageLogRepositoryInterface $usageLogRepo, Manager $manager)
    {
        $this->applicationRepo = $applicationRepo;
        $this->manager = $manager;
        $this->usageLogRepo = $usageLogRepo;
    }

    /**
     * @OA\Get(
     *     path="/usage/applications",
     *     tags={"UsageLogs"},
     *     summary="Get application usage logs",
     *     operationId="getApplicationLogs",
     *     security={},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="fromDate",
     *         in="query",
     *         required=false,
     *         description="Start date for filtering logs (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="toDate",
     *         in="query",
     *         required=false,
     *         description="End date for filtering logs (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getApplicationLogs(Request $request)
    {
        $this->validate($request, [
            'fromDate' => 'sometimes|date',
            'toDate' => 'sometimes|date',
            'orderBy' => 'sometimes|string|in:name,username,estimatedUsers,requestCount',
            'sort' => 'sometimes|string|in:asc,desc',
        ]);

        try {
            $orderBy = $request->query('orderBy', 'name');
            $sort = strtolower($request->query('sort', 'asc')) === 'desc';
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
        } catch (\Exception $e) {
            Log::error('Could not get Usage Log', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Usage Log',
                'errors' => [],
            ], 500);
        }
        $usageLogs = $usageLogs->sortBy($orderBy, SORT_REGULAR, $sort)->values();
        $paginated = $usageLogs->paginate(10)->toArray();

        // TODO: Create custom paginator class
        unset($paginated['first_page_url']);
        unset($paginated['last_page_url']);
        unset($paginated['next_page_url']);
        unset($paginated['prev_page_url']);

        return response()->json([
            'data' => $paginated,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/usage/endpoints",
     *     tags={"UsageLogs"},
     *     summary="Get endpoint usage logs",
     *     operationId="getEndpointLogs",
     *     security={},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="fromDate",
     *         in="query",
     *         required=false,
     *         description="Start date for filtering logs (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="toDate",
     *         in="query",
     *         required=false,
     *         description="End date for filtering logs (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getEndpointLogs(Request $request)
    {
        $this->validate($request, [
            'fromDate' => 'sometimes|date',
            'toDate' => 'sometimes|date',
        ]);

        try {
            $usageLogs = $this->usageLogRepo->listByEndpoint($request->fromDate, $request->toDate);

            $usageLogs = $usageLogs->map(function ($usageLog) {
                $application = $this->applicationRepo->find($usageLog->application_id);

                $usageLog->application_name = $application['name'] ?? 'Unknown';
                $usageLog->application_tenant_user_id = $application['tenant_user_id'] ?? null;

                //Log::info(json_encode($application));

                return $usageLog;
            });
        } catch (\Exception $e) {
            Log::error('Could not get Usage Log', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Usage Log',
                'errors' => [],
            ], 500);
        }

        $paginated = $usageLogs->paginate(10)->toArray();

        unset($paginated['first_page_url']);
        unset($paginated['last_page_url']);
        unset($paginated['next_page_url']);
        unset($paginated['prev_page_url']);

        return response()->json([
            'data' => $usageLogs,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/usage/export",
     *     tags={"UsageLogs"},
     *     summary="Export usage logs as CSV",
     *     operationId="exportUsageLogs",
     *     security={},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="fromDate",
     *         in="query",
     *         required=false,
     *         description="Start date for filtering logs (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="toDate",
     *         in="query",
     *         required=false,
     *         description="End date for filtering logs (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function export(Request $request)
    {
        $this->validate($request, [
            'fromDate' => 'sometimes|date',
            'toDate' => 'sometimes|date',
        ]);

        try {
            $usageLogs = $this->usageLogRepo->whereBetween($request->fromDate, $request->toDate, ['*']);
        } catch (\Exception $e) {
            Log::error('Could not export Usage Logs', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not export Usage Logs',
                'errors' => [],
            ], 500);
        }

        // Create CSV
        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());

        $csv->insertOne([
            'Application Name',
            'Endpoint',
            'Method',
            'Timestamp',
        ]);

        $applications = $this->applicationRepo->all();

        // Insert usage log records
        foreach ($usageLogs as $usageLog) {
            $application = $applications->where('id', $usageLog->application_id)->first();

            $csv->insertOne([
                $application ? $application->name : 'Unknown',
                $usageLog->endpoint,
                $usageLog->method,
                $usageLog->timestamp,
            ]);
        }

        // Print raw CSV as response
        print_r($csv->toString());
    }

    /**
     * @param int $applicationId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getForApplication(int $applicationId)
    {
        try {
            // Cache to be enabled in production
            // $usageLogs = Cache::remember("usage.application.{$applicationId}", 3600, function () use ($applicationId) {
            //     return $this->usageLogRepo->getForApplication($applicationId);
            // });

            $usageLogs = $this->usageLogRepo->getForApplication($applicationId);
        } catch (\Exception $e) {
            Log::error('Could not get Usage Logs for application', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Usage Logs for application',
                'errors' => [],
            ], 500);
        }

        return response()->json([
            'count' => count($usageLogs),
        ], 200);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Get(
     *     path="/usage/totals",
     *     tags={"UsageLogs"},
     *     summary="Get usage log totals",
     *     operationId="getTotals",
     *     security={},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="society",
     *         in="query",
     *         required=false,
     *         description="Filter by society",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="subnational",
     *         in="query",
     *         required=false,
     *         description="Filter by subnational ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hazard",
     *         in="query",
     *         required=false,
     *         description="Filter by hazard type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=false,
     *         description="Filter by specific date (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         required=false,
     *         description="Filter by language",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getTotals(Request $request)
    {
        $this->validate($request, [
            'society' => 'sometimes|string',
            'subnational' => 'sometimes|int',
            'hazard' => 'sometimes|string',
            'date' => 'sometimes|date',
            'language' => 'sometimes|string',
        ]);

        try {
            $usageLog = new UsageLog;
            $query = $usageLog->query();

            if ($request->has('society')) {
                $query->where('endpoint', 'v2/org/' . $request->society . '/whatnow');
            }
            if ($request->has('subnational')) {
                $query->where('subnational', $request->subnational);
            }
            if ($request->has('hazard')) {
                $query->where('event_type', 'like', '%' . $request->hazard . '%');
            }
            if ($request->has('date')) {
                $query->whereDate('timestamp', $request->date);
            }
            if ($request->has('language')) {
                $query->where('language', $request->language);
            }

            $stats = $query->selectRaw('COUNT(*) as hits, COUNT(DISTINCT application_id) as unique_apps')
                ->first();


            $applicationQuery = $usageLog->query();
            if ($request->has('society')) {
                $applicationQuery->where('endpoint', 'v2/org/' . $request->society . '/whatnow');
            }
            if ($request->has('subnational')) {
                $applicationQuery->where('subnational', $request->subnational);
            }
            if ($request->has('hazard')) {
                $applicationQuery->where('event_type', 'like', '%' . $request->hazard . '%');
            }
            if ($request->has('date')) {
                $applicationQuery->whereDate('timestamp', $request->date);
            }
            if ($request->has('language')) {
                $applicationQuery->where('language', $request->language);
            }

            $uniqueApplicationIds = $applicationQuery->select('application_id')
                ->distinct()
                ->pluck('application_id')
                ->toArray();

            $totalEstimatedUsers = 0;
            if (!empty($uniqueApplicationIds)) {
                $totalEstimatedUsers = $this->applicationRepo->findIn($uniqueApplicationIds)
                    ->sum('estimated_users_count');
            }

            $totals = [
                'applications' => $stats->unique_apps,
                'estimatedUsers' => $totalEstimatedUsers,
                'hits' => $stats->hits,
            ];


        } catch (\Exception $e) {
            \Log::error('Could not get Usage Log totals', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Usage Log totals',
                'errors' => [],
            ], 500);
        }

        return response()->json([
            'data' => $totals,
        ], 200);
    }
}

