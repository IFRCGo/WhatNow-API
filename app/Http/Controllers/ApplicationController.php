<?php

namespace App\Http\Controllers;

use App\Classes\Repositories\ApplicationRepositoryInterface;
use App\Classes\Transformers\ApplicationTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;

/**
 * @OA\Tag(
 *     name="Aplications",
 *     description="Operations about Aplications"
 * )
 */
class ApplicationController extends Controller
{
    /**
     * @var ApplicationRepositoryInterface
     */
    protected $repo;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * When more tenants are added, this will be inferred from the auth token that was used.
     *
     * @var int
     */
    private $tenantId = 1;

    /**
     * Create a new controller instance.
     *
     * @param ApplicationRepositoryInterface $repo
     * @param Request $request
     * @param Manager $manager
     */
    public function __construct(
        ApplicationRepositoryInterface $repo,
        Request $request,
        Manager $manager
    ) {
        $this->repo = $repo;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Get(
     *     path="/apps",
     *     tags={"Applications"},
     *     summary="Get all applications for a user",
     *     operationId="getAllForUser",
     *     @OA\Parameter(
     *         name="userId",
     *         in="query",
     *         required=true,
     *         description="ID of the user to fetch applications for",
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
    public function getAllForUser(Request $request)
    {
        $this->validate($this->request, [
            'userId' => 'required|string',
        ]);

        $userId = $request->get('userId', null);
        $tenantId = $this->tenantId;

        try {
            /** @var Collection $apps */
            $apps = $this->repo->findForUserId($tenantId, $userId);
        } catch (\Exception $e) {
            Log::error('Could not get Applications list for user', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Applications list',
                'errors' => [],
            ], 500);
        }

        $resource = new \League\Fractal\Resource\Collection($apps, new ApplicationTransformer);
        $response = $this->manager->createData($resource);

        return response()->json($response->toArray(), 200);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Get(
     *     path="/apps/{id}",
     *     tags={"Applications"},
     *     summary="Get an application by ID",
     *     operationId="getApplicationById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the application to retrieve",
     *         @OA\Schema(type="integer", format="int64")
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
    public function getById($id)
    {
        try {
            $application = $this->repo->find($id);
        } catch (\Exception $e) {
            Log::error('Application not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 404,
                'error_message' => 'Application does not exist',
                'errors' => ['No matching Application'],
            ], 404);
        }

        if ($application->tenant_id !== $this->tenantId) {
            return response()->json([
                'status' => 403,
                'error_message' => 'Application does not belong to tenant',
                'errors' => ['Application does not belong to tenant'],
            ], 403);
        }

        $resource = new \League\Fractal\Resource\Item($application, new ApplicationTransformer());
        $response = $this->manager->createData($resource);

        return response()->json($response->toArray(), 200);
    }

    /**
     * Creates an Application Entity
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Post(
     *     path="/apps",
     *     tags={"Applications"},
     *     summary="Create a new application",
     *     operationId="createApplication",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "userId"},
     *             @OA\Property(property="name", type="string", example="My Application", description="Name of the application"),
     *             @OA\Property(property="description", type="string", example="A description of the application", description="Description of the application (optional)"),
     *             @OA\Property(property="userId", type="string", example="user123", description="ID of the user creating the application"),
     *             @OA\Property(property="estimatedUsers", type="integer", example=100, description="Estimated number of users (optional)")
     *         )
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
    public function create(Request $request)
    {
        $this->validate($this->request, [
            'name' => 'required|string',
            'description' => 'string',
            'userId' => 'required|string',
            'estimatedUsers' => 'sometimes|integer',
        ]);


        $data = $this->request->except('userId');
        $data['estimated_users_count'] = $request->get('estimatedUsers', 0);
        $data['tenant_user_id'] = $request->get('userId');
        $data['tenant_id'] = $this->tenantId;

        try {
            $application = $this->repo->create($data);
        } catch (\Exception $e) {
            Log::error('Application not created', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Unable to create Application',
                'errors' => [$e->getMessage()],
            ], 500);
        }

        $resource = new \League\Fractal\Resource\Item($application, new ApplicationTransformer());
        $response = $this->manager->createData($resource);

        return response()->json($response->toArray(), 201);
    }

    /**
     * @OA\Patch(
     *     path="/apps/{id}",
     *     tags={"Applications"},
     *     summary="Update an application by ID",
     *     operationId="updateApplication",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the application to update",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="estimatedUsers", type="integer", example=100, description="Estimated number of users (optional)")
     *         )
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
    public function update(Request $request, $id)
    {
        $this->validate($this->request, [
            'estimatedUsers' => 'sometimes|integer',
        ]);

        $data = [];
        $data['estimated_users_count'] = $request->get('estimatedUsers', 0);

        try {
            $this->repo->updateWithIdAndInput($id, $data);
        } catch (\Exception $e) {
            Log::error('Application not updated', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Unable to update Application',
                'errors' => [$e->getMessage()],
            ], 500);
        }

        $application = $this->repo->find($id);

        $resource = new \League\Fractal\Resource\Item($application, new ApplicationTransformer());
        $response = $this->manager->createData($resource);

        return response()->json($response->toArray(), 201);
    }

    /**
     * @OA\Delete(
     *     path="/apps/{id}",
     *     tags={"Applications"},
     *     summary="Delete an application by ID",
     *     operationId="deleteApplication",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the application to delete",
     *         @OA\Schema(type="integer", format="int64")
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
    public function delete($id)
    {
        try {
            $application = $this->repo->find($id);
        } catch (\Exception $e) {
            Log::error('Application not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 404,
                'error_message' => 'Application does not exist',
                'errors' => ['No matching Application'],
            ], 404);
        }

        if ($application->tenant_id !== $this->tenantId) {
            return response()->json([
                'status' => 403,
                'error_message' => 'Application does not belong to tenant',
                'errors' => ['Application does not belong to tenant'],
            ], 403);
        }

        try {
            $this->repo->destroy($id);
        } catch (\Exception $e) {
            Log::error('Application not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 404,
                'error_message' => 'Application does not exist',
                'errors' => ['No matching Application'],
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Application deleted',
        ], 200);
    }
}
