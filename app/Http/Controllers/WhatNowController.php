<?php

namespace App\Http\Controllers;

use App\Classes\Feeds\WhatNowFeed;
use App\Classes\Repositories\OrganisationRepositoryInterface;
use App\Classes\Repositories\RegionRepositoryInterface;
use App\Classes\Repositories\WhatNowRepositoryInterface;
use App\Classes\Repositories\WhatNowTranslationRepositoryInterface;
use App\Classes\Serializers\CustomDataSerializer;
use App\Classes\Transformers\WhatNowEntityTransformer;
use App\Models\Region;
use App\Models\WhatNowEntity;
use App\Models\WhatNowEntityTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

/**
 * @OA\Tag(
 *     name="Whatnow",
 *     description="Operations about Whatnow messages"
 * )
 */
class WhatNowController extends Controller
{
    /**
     * @var OrganisationRepositoryInterface
     */
    protected $orgRepo;

    /**
     * @var RegionRepositoryInterface
     */
    protected $regionRepo;

    /**
     * @var WhatNowRepositoryInterface
     */
    protected $wnRepo;

    /**
     * @var WhatNowTranslationRepositoryInterface
     */
    protected $wnTransRepo;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Create a new controller instance.
     *
     * @param OrganisationRepositoryInterface $orgRepo
     * @param WhatNowRepositoryInterface $wnRepo
     * @param WhatNowTranslationRepositoryInterface $wnTransRepo
     * @param Request $request
     * @param Manager $manager
     */
    public function __construct(
        OrganisationRepositoryInterface $orgRepo,
        RegionRepositoryInterface $regionRepo,
        WhatNowRepositoryInterface $wnRepo,
        WhatNowTranslationRepositoryInterface $wnTransRepo,
        Request $request,
        Manager $manager
    ) {
        $this->orgRepo = $orgRepo;
        $this->regionRepo = $regionRepo;
        $this->wnRepo = $wnRepo;
        $this->wnTransRepo = $wnTransRepo;
        $this->request = $request;
        $this->manager = $manager;
        $this->manager->setSerializer(new CustomDataSerializer());
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Get(
     *     path="/whatnow/{id}",
     *     tags={"Whatnow"},
     *     summary="Obtiene un recurso publicado por ID (public)",
     *     description="Retorna los detalles de un recurso publicado basado en el ID proporcionado.",
     *     operationId="getPublishedById",
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del recurso publicado",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
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
    public function getPublishedById($id)
    {
        try {
            /** @var WhatNowEntity $entity */
            $entity = $this->wnRepo->find($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error_message' => 'Entity not found',
                'errors' => ['Entity not found'],
            ], 404);
        }

        if ($this->wnTransRepo->getLatestPublishedTranslations($entity->id)->count() === 0) {
            return response()->json([
                'status' => 404,
                'error_message' => 'Entity not found',
                'errors' => ['Entity not found'],
            ], 404);
        }

        // Load related organisation
        $entity->load('organisation');
        $entity->load('organisation.details');

        // Transform model into required json response structure. Correctly cast data types etc.
        $resource = new Item($entity, new WhatNowEntityTransformer($this->wnTransRepo));
        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 200);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Get(
     *     path="/whatnow/{id}/revisions/latest",
     *     tags={"Whatnow"},
     *     summary="Get the latest revision of a WhatNow entity by ID",
     *     operationId="getLatestById",
     *     security={},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the WhatNow entity to fetch the latest revision for",
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
    public function getLatestById($id)
    {
        try {
            /** @var WhatNowEntity $entity */
            $entity = $this->wnRepo->find($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error_message' => 'Entity not found',
                'errors' => ['Entity not found'],
            ], 404);
        }

        // Load related organisation
        $entity->load('organisation');
        $entity->load('organisation.details');

        $entity->load('translations');

        // Transform model into required json response structure. Correctly cast data types etc.
        $resource = new Item($entity, new WhatNowEntityTransformer($this->wnTransRepo, [
            'unpublished' => true,
        ]));
        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 200);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Delete(
     *     path="/whatnow/{id}",
     *     tags={"Whatnow"},
     *     summary="Delete a WhatNow entity by ID",
     *     operationId="deleteById",
     *     security={},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the WhatNow entity to delete",
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
    public function deleteById($id)
    {
        try {
            $entity = $this->wnRepo->find($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error_message' => 'Entity not found',
                'errors' => ['Entity not found'],
            ]);
        }

        $entity->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Entity deleted',
        ], 200);
    }

    /**
     * @param WhatNowFeed $feed
     * @param $code
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Get(
     *     path="/org/{code}/whatnow",
     *     tags={"Whatnow"},
     *     summary="Get a feed of WhatNow entities for a specific organisation (public)",
     *     operationId="getFeed",
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="Country code of the organisation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="region",
     *         in="query",
     *         required=false,
     *         description="Filter by region slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="language",
     *         in="query",
     *         required=false,
     *         description="Filter by language code",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="eventType",
     *         in="query",
     *         required=false,
     *         description="Filter by event type",
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
    public function getFeed(WhatNowFeed $feed, $code)
    {
        try {
            try {
                $org = $this->orgRepo->findByCountryCode(strtoupper($code));
            } catch (\Exception $e) {
                Log::error('Organisation not found', ['message' => $e->getMessage()]);
                $this->changeLogStatus(404);
                return response()->json(['message' => 'Organisation not found'], 404);
            }
            $feed->setOrganisation($org);

            $regName = $this->request->query('region', null);
            if ($regName) {
                try {
                    $reg = $this->regionRepo->findBySlug($org->id, $regName);
                    $feed->setRegion($reg);
                } catch (\Exception $e) {
                    Log::error('Region not found', ['message' => $e->getMessage()]);
                    $this->changeLogStatus(404);
                    return response()->json(['message' => 'Region not found'], 404);
                }
            }

            $langParam = $this->request->query('language', null);
            $langHeader = $this->request->header('Accept-Language', null);

            if ($langParam) {
                $feed->setLanguage($langParam);
            } elseif ($langHeader) {
                $feed->setLanguage(locale_accept_from_http($langHeader));
            }

            $feed->setEventTypeFilter($this->request->query('eventType', null));
            $feed->loadData();
            $data = $feed->getResponseData();
            if(empty($data)){
                $this->changeLogStatus(204);
            }
            return response()->json(['data' => $data]);
        }catch(\Exception $e){
            $this->changeLogStatus(500);
            return response()->json(['message' => $e], 500);
        }
    }

    protected function changeLogStatus($status){
        if(isset($this->request->usageLog)){
            $this->request->usageLog->code_status = $status;
            $this->request->usageLog->save();
        }
    }

    /**
     * @param $code
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Get(
     *     path="/org/{code}/whatnow/revisions/latest",
     *     tags={"Whatnow"},
     *     summary="Get the latest revisions for a country code",
     *     operationId="getLatestForCountryCode",
     *     security={},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="Country code to fetch the latest revisions for",
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
    public function getLatestForCountryCode($code)
    {
        try {
            $org = $this->orgRepo->findByCountryCode(strtoupper($code));
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response(null, 404);
        }

        // todo: get language?

        /** @var \Illuminate\Database\Eloquent\Collection $items */
        $items = $this->wnRepo->findItemsForOrgId(
            $org->id
        );

        $resource = new Collection($items, new WhatNowEntityTransformer($this->wnTransRepo, [
            'unpublished' => true,
        ]));
        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 200);
    }

    /**
     * @param $code
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Get(
     *     path="/org/{code}/{region}/whatnow/revisions/latest",
     *     tags={"Whatnow"},
     *     summary="Get the latest revisions for a specific region",
     *     operationId="getLatestForRegion",
     *     security={},
     *     deprecated=true,
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="Country code to fetch the latest revisions for",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="region",
     *         in="path",
     *         required=true,
     *         description="Region slug to fetch the latest revisions for",
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
    public function getLatestForRegion($code, $region)
    {
        try {
            $org = $this->orgRepo->findByCountryCode(strtoupper($code));
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response(null, 404);
        }

        try {
            $region = $org->regions()->where('slug', str_slug($region))->firstOrFail();
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response(null, 404);
        }

        /** @var \Illuminate\Database\Eloquent\Collection $items */
        $items = $this->wnRepo->findItemsForRegionByOrgId(
            $org->id,
            null,
            [],
            $region->title
        );

        $resource = new Collection($items, new WhatNowEntityTransformer($this->wnTransRepo, [
            'unpublished' => true,
        ]));
        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 200);
    }

    /**
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getAllRevisions()
    {
        /** @var \Illuminate\Database\Eloquent\Collection $items */
        $items = $this->wnRepo->all();

        $resource = new Collection($items, new WhatNowEntityTransformer($this->wnTransRepo, [
            'unpublished' => true,
            'castDateToBoolean' => false,
        ]));

        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 200);
    }

    /**
     * Creates a new WhatNow Entity
     */
    /**
     * @OA\Post(
     *     path="/whatnow",
     *     tags={"Whatnow"},
     *     summary="Create a new WhatNow entity",
     *     operationId="createWhatNowEntity",
     *     security={},
     *     deprecated=true,
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"countryCode", "eventType", "translations"},
     *             @OA\Property(property="countryCode", type="string", example="USA", description="Country code (3 characters)"),
     *             @OA\Property(property="eventType", type="string", example="Flood", description="Type of event (max 50 characters)"),
     *             @OA\Property(property="regionName", type="string", example="North Region", description="Name of the region (optional)"),
     *             @OA\Property(
     *                 property="translations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="webUrl", type="string", format="url", example="https://example.com", description="Web URL for the translation (optional)"),
     *                     @OA\Property(property="lang", type="string", example="en", description="Language code (2 characters)"),
     *                     @OA\Property(property="title", type="string", example="Flood Alert", description="Title in the specified language"),
     *                     @OA\Property(property="description", type="string", example="Description of the event", description="Description in the specified language")
     *                 )
     *             )
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
    public function post()
    {
        try {
            $this->validate($this->request, [
                'countryCode' => 'string|size:3',
                'eventType' => 'string|max:50',
                'regionName' => 'nullable|string',
                'translations' => 'array',
                'translations.*.webUrl' => 'nullable|url',
                'translations.*.lang' => 'alpha|size:2',
                'translations.*.title' => 'string',
                'translations.*.description' => 'string',
            ]);
        } catch (ValidationException $e) {
            $errors = collect($e->errors());
            Log::error(print_r($errors, true));

            return $e->getResponse();
        }

        try {
            $org = $this->orgRepo->findByCountryCode($this->request->input('countryCode'));
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Unable to create item',
                'errors' => ['No matching organisation for country code'],
            ], 500);
        }

        if (! empty($this->request->input('regionName') && strtolower($this->request->input('regionName')) !== 'national')) {
            try {
                $region = Region::where('organisation_id', '=', $org->id)
                    ->where('title', '=', $this->request->input('regionName'))
                    ->firstOrFail();
            } catch (\Exception $e) {
                Log::error('Region not found', ['message' => $e->getMessage()]);

                return response()->json([
                    'status' => 500,
                    'error_message' => 'Unable to create item',
                    'errors' => ['No matching region for country code'],
                ], 500);
            }
        }

        $eventType = $this->request->get('eventType');
        $regionName = $this->request->get('regionName');

        /** @var \Illuminate\Database\Eloquent\Collection $exists */
        $exists = $this->wnRepo->findItemsForRegionByOrgId($org->id, null, [$eventType], $regionName);
        if ($exists->count() > 0) {
            return response()->json([
                'status' => 409,
                'error_message' => 'Entity already exists',
                'errors' => ['An entity for organisation '.$org->org_name.' and event type '.$eventType.' already exists'],
            ], 409);
        }

        $attributes = $this->request->all();

        Log::info($attributes);

        $attributes['orgId'] = $org->id;
        empty($region) ?: $attributes['region_id'] = $region->id;

        try {
            $entity = $this->wnRepo->createFromArray($attributes);
        } catch (QueryException $e) {
            Log::error('Error saving Entity', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Error saving Entity',
                'errors' => ['Error saving Entity'],
            ], 500);
        }

        Log::info($entity);

        // Transform model into required json response structure. Correctly cast data types etc.
        $resource = new Item($entity, new WhatNowEntityTransformer($this->wnTransRepo, [
            'unpublished' => true,
        ]));

        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 201);
    }

    /**
     * @OA\Put(
     *     path="/whatnow/{id}",
     *     tags={"Whatnow"},
     *     summary="Update a WhatNow entity by ID",
     *     operationId="putById",
     *     deprecated=true,
     *     security={},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the WhatNow entity to update",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"countryCode", "eventType", "translations"},
     *             @OA\Property(property="countryCode", type="string", example="USA", description="Country code (3 characters)"),
     *             @OA\Property(property="eventType", type="string", example="Flood", description="Type of event (max 50 characters)"),
     *             @OA\Property(property="regionName", type="string", example="North Region", description="Name of the region (optional)"),
     *             @OA\Property(
     *                 property="translations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="webUrl", type="string", example="https://example.com", description="Web URL for the translation (optional)"),
     *                     @OA\Property(property="lang", type="string", example="en", description="Language code (2 characters)"),
     *                     @OA\Property(property="title", type="string", example="Flood Alert", description="Title in the specified language"),
     *                     @OA\Property(property="description", type="string", example="Description of the event", description="Description in the specified language")
     *                 )
     *             )
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
    public function putById($id)
    {
        try {
            $entity = $this->wnRepo->find($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error_message' => 'Entity not found',
                'errors' => ['Entity not found'],
            ]);
        }

        // try {
        //     $this->validate($this->request, [
        //         'countryCode' => 'alpha|size:3',
        //         'eventType' => 'string|max:50',
        //         'regionName' => 'nullable|string',
        //         'translations' => 'array',
        //         'translations.*.webUrl' => 'nullable|string',
        //         'translations.*.lang' => 'alpha|size:2',
        //         'translations.*.title' => 'string',
        //         'translations.*.description' => 'string',
        //     ]);
        // } catch (ValidationException $e) {
        //     Log::info($e->getMessage());

        //     return $e->getResponse();
        // }

        try {
            $org = $this->orgRepo->findByCountryCode($this->request->input('countryCode'));
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Unable to create item',
                'errors' => ['No matching organisation for country code'],
            ], 500);
        }

        if (! empty($this->request->input('regionName')) && strtolower($this->request->input('regionName')) !== 'national') {
            try {
                $region = Region::where('organisation_id', '=', $org->id)
                    ->where('title', '=', $this->request->input('regionName'))
                    ->firstOrFail();
            } catch (\Exception $e) {
                Log::error('Region not found', ['message' => $e->getMessage()]);

                return response()->json([
                    'status' => 500,
                    'error_message' => 'Unable to create item',
                    'errors' => ['No matching region for country code'],
                ], 500);
            }
        }

        $attributes = $this->request->all();
        $attributes['orgId'] = $org->id;
        empty($region) ?: $attributes['region_id'] = $region->id;

        try {
            $this->wnRepo->updateWithIdAndInput($entity->id, $attributes);
        } catch(\Exception $e) {
            Log::error('Error updating item', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => $e->getMessage(),
                'errors' => ['Error updating item'],
            ], 500);
        }

        // Transform model into required json response structure. Correctly cast data types etc.
        $resource = new Item($entity->fresh(), new WhatNowEntityTransformer($this->wnTransRepo, [
            'unpublished' => true,
        ]));

        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 200);
    }

    /**
     * @OA\Post(
     *     path="/whatnow/{id}/revisions",
     *     tags={"Whatnow"},
     *     summary="Create a new translation for a WhatNow entity",
     *     operationId="createNewTranslation",
     *     deprecated=true,
     *     security={},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the WhatNow entity to add a translation to",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"webUrl", "lang", "title", "stages"},
     *             @OA\Property(property="webUrl", type="string", format="url", example="https://example.com", description="Web URL for the translation"),
     *             @OA\Property(property="lang", type="string", example="en", description="Language code (2 characters)"),
     *             @OA\Property(property="title", type="string", example="Flood Alert", description="Title in the specified language"),
     *             @OA\Property(property="description", type="string", example="Description of the event", description="Description in the specified language (optional)"),
     *             @OA\Property(property="stages", type="array", @OA\Items(type="object"), description="Stages associated with the translation")
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
    public function createNewTranslation($id)
    {
        try {
            $entity = $this->wnRepo->find($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error_message' => 'Entity not found',
                'errors' => ['Entity not found'],
            ]);
        }

        try {
            $this->validate($this->request, [
                'webUrl' => 'url',
                'lang' => 'alpha|size:2',
                'title' => 'string',
                'description' => 'string',
                'stages' => 'array',
            ]);
        } catch (ValidationException $e) {
            return $e->getResponse();
        }

        try {
            $this->wnTransRepo->addTranslations($entity, [[
                'webUrl' => $this->request->get('webUrl'),
                'lang' => $this->request->get('lang'),
                'title' => $this->request->get('title'),
                'description' => $this->request->get('description', null),
                'stages' => $this->request->get('stages'),
            ]]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 500,
                'error_message' => 'Unable to update translation',
                'errors' => ['Error updating translation'],
            ], 500);
        }

        // Transform model into required json response structure. Correctly cast data types etc.
        $resource = new Item($entity, new WhatNowEntityTransformer($this->wnTransRepo, [
            'unpublished' => true,
        ]));

        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 201);
    }

    /**
     * @param int $id
     * @param int $translationId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * @OA\Patch(
     *     path="/whatnow/{id}/revisions/{translationId}",
     *     tags={"Whatnow"},
     *     summary="Update the published status of a translation",
     *     operationId="patchTranslation",
     *     deprecated=true,
     *     security={},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the WhatNow entity",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Parameter(
     *         name="translationId",
     *         in="path",
     *         required=true,
     *         description="ID of the translation to update",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"published"},
     *             @OA\Property(property="published", type="boolean", example=true, description="Publish status of the translation")
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
    public function patchTranslation($id, $translationId)
    {
        $this->validate($this->request, [
            'published' => 'required|bool',
        ]);

        try {
            /** @var WhatNowEntity $entity */
            $entity = $this->wnRepo->find($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error_message' => 'Entity not found',
                'errors' => ['Entity not found'],
            ]);
        }

        try {
            /** @var WhatNowEntityTranslation $translation */
            $translation = $this->wnTransRepo->getTranslationById($translationId);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'error_message' => 'Translation not found',
                'errors' => ['Translation not found'],
            ]);
        }

        $publish = $this->request->get('published');

        if ($publish === true) {
            $translation->publish();
        } else {
            $translation->revert();
        }

        // Transform model into required json response structure. Correctly cast data types etc.
        $resource = new Item($entity, new WhatNowEntityTransformer($this->wnTransRepo, [
            'unpublished' => true,
        ]));

        $response = $this->manager->createData($resource);

        return response()->json(['data' => $response->toArray()], 200);
    }

    /**
     * @OA\Post(
     *     path="/whatnow/publish",
     *     tags={"Whatnow"},
     *     summary="Publish translations by IDs",
     *     operationId="publishTranslationsByIds",
     *     deprecated=true,
     *     security={},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"translationIds"},
     *             @OA\Property(
     *                 property="translationIds",
     *                 type="array",
     *                 @OA\Items(type="integer", format="int64"),
     *                 description="Array of translation IDs to publish"
     *             )
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
    public function publishTranslationsByIds()
    {
        $this->validate($this->request, [
            'translationIds' => 'array',
        ]);

        try {
            $this->wnTransRepo->publishTranslationsById($this->request->get('translationIds'));
        } catch(\Exception $e) {
            return response()->json([
                'status' => 500,
                'error_message' => 'Unable to update translations',
                'errors' => ['Error updating translations'],
            ], 500);
        }

        return response()->json(['success'], 200);
    }
}
