<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Aplications",
 *     description="Operations about Aplications"
 * )
 */
class ApplicationController extends Controller
{
    /**
     * Get all active applications for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/apps",
     *     tags={"Applications"},
     *     summary="Get all applications for a user",
     *     operationId="getAllForUser",
     *     security={},
     *     deprecated=true,
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
        try {
            // Obtener el userId del parámetro de query
            $userId = $request->query('userId');

            // Validar que se proporcione el userId
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'userId parameter is required'
                ], 400);
            }

            $applications = Application::where('tenant_user_id', $userId)
                ->active()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $applications,
                'message' => 'Applications retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving applications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific active application by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/apps/{id}",
     *     tags={"Applications"},
     *     summary="Get an application by ID",
     *     operationId="getApplicationById",
     *     security={},
     *     deprecated=true,
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
    public function getById(Request $request, $id)
    {
        try {
            // Obtener el userId del parámetro de query
            $userId = $request->query('userId');

            // Validar que se proporcione el userId
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'userId parameter is required'
                ], 400);
            }

            $application = Application::where('id', $id)
                ->where('tenant_user_id', $userId)
                ->active()
                ->first();

            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active application not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $application,
                'message' => 'Application retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving application: ' . $e->getMessage()
            ], 500);
        }
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
     *     security={},
     *     deprecated=true,
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
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'estimated_users_count' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
                'userId' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener el userId del request
            $userId = $request->input('userId');

            // Generar la clave API
            $apiKey = Application::generateKey();

            // Verificar que la clave se generó correctamente
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate API key'
                ], 500);
            }

            $application = Application::create([
                'tenant_id' => 1,
                'tenant_user_id' => $userId,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'estimated_users_count' => $request->input('estimated_users_count', $request->input('estimatedUsers', 0)),
                'key' => $apiKey,
                'is_active' => $request->input('is_active', true)
            ]);

            return response()->json([
                'success' => true,
                'data' => $application,
                'message' => 'Application created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/apps/{id}",
     *     tags={"Applications"},
     *     summary="Update an application by ID",
     *     operationId="updateApplication",
     *     security={},
     *     deprecated=true,
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
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'estimated_users_count' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
                'userId' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener el userId del request
            $userId = $request->input('userId');

            $application = Application::where('id', $id)
                ->where('tenant_user_id', $userId)
                ->first();

            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }

            // Actualizar solo los campos proporcionados
            $updateData = array_filter($request->only([
                'name',
                'description',
                'estimated_users_count',
                'is_active'
            ]), function($value) {
                return $value !== null;
            });

            $application->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $application->fresh(),
                'message' => 'Application updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/apps/{id}",
     *     tags={"Applications"},
     *     summary="Delete an application by ID",
     *     operationId="deleteApplication",
     *     security={},
     *     deprecated=true,
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
    public function delete(Request $request, $id)
    {
        try {

            $userId = $request->query('userId');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'userId parameter is required'
                ], 400);
            }

            $application = Application::where('id', $id)
                ->where('tenant_user_id', $userId)
                ->first();

            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }

            $application->delete();

            return response()->json([
                'success' => true,
                'message' => 'Application deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all applications for admin (including inactive ones)
     * This method can be used by administrators to see all applications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllForAdmin(Request $request)
    {
        try {
            $applications = Application::all();

            return response()->json([
                'success' => true,
                'data' => $applications,
                'message' => 'All applications retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving applications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate all applications for a specific user
     *
     * @param int $id - User ID (tenant_user_id)
     * @return JsonResponse
     */
    public function activate(Request $request, $id)
    {
        try {

            $userId = $id;


            $applications = Application::where('tenant_user_id', $userId)->get();

            if ($applications->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No applications found for this user'
                ], 404);
            }

            $updatedCount = Application::where('tenant_user_id', $userId)
                ->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'updated_applications_count' => $updatedCount
                ],
                'message' => 'All applications activated successfully for user ' . $userId
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error activating applications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate all applications for a specific user
     *
     * @param int $id - User ID (tenant_user_id)
     * @return JsonResponse
     */
    public function deactivate(Request $request, $id)
    {
        try {

            $userId = $id;

            $applications = Application::where('tenant_user_id', $userId)->get();

            if ($applications->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No applications found for this user'
                ], 404);
            }

            $updatedCount = Application::where('tenant_user_id', $userId)
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'updated_applications_count' => $updatedCount
                ],
                'message' => 'All applications deactivated successfully for user ' . $userId
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deactivating applications: ' . $e->getMessage()
            ], 500);
        }
    }
}
