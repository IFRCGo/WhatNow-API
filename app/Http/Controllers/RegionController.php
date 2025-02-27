<?php

namespace App\Http\Controllers;

use App\Classes\Repositories\OrganisationRepositoryInterface;
use App\Classes\Repositories\RegionRepositoryInterface;
use App\Models\Organisation;
use App\Models\Region;
use App\Models\RegionTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
/**
 * @OA\Tag(
 *     name="Regions",
 *     description="Operations about Regions"
 * )
 */
class RegionController extends Controller
{
    /**
     * @var RegionRepositoryInterface
     */
    protected $regRepo;

    /**
     * @var OrganisationRepositoryInterface
     */
    protected $orgRepo;

    public function __construct(
        OrganisationRepositoryInterface $orgRepo,
        RegionRepositoryInterface $regRepo
    )
    {
        $this->orgRepo = $orgRepo;
        $this->regRepo = $regRepo;
    }

    /**
     * @OA\Post(
     *     path="/regions",
     *     tags={"Regions"},
     *     summary="Create a new region",
     *     operationId="createRegion",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"countryCode", "title"},
     *             @OA\Property(property="countryCode", type="string", example="USA", description="Country code (3 characters)"),
     *             @OA\Property(property="title", type="string", example="North America", description="Title of the region"),
     *             @OA\Property(property="slug", type="string", example="north-america", description="Slug for the region (optional)"),
     *             @OA\Property(
     *                 property="translations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="webUrl", type="string", format="url", example="https://example.com", description="Web URL for the translation"),
     *                     @OA\Property(property="lang", type="string", example="en", description="Language code (2 characters)"),
     *                     @OA\Property(property="title", type="string", example="North America", description="Title in the specified language"),
     *                     @OA\Property(property="description", type="string", example="Description of the region", description="Description in the specified language")
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
    public function createRegion(Request $request)
    {
        try {
            $this->validate($request, [
                'countryCode' => 'required|string|size:3',
                'title' => 'required|string',
                'slug' => 'sometimes|string',
                'translations' => 'array',
                'translations.*.webUrl' => 'url',
                'translations.*.lang' => 'alpha|size:2',
                'translations.*.title' => 'string',
                'translations.*.description' => 'string',
            ]);
        } catch (ValidationException $e) {
            return $e->getResponse();
        }

        try {
            $org = $this->orgRepo->findByCountryCode($request->input('countryCode'));
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => $e->getMessage(),
                'errors' => ['No matching organisation for country code'],
            ], 500);
        }

        $slug = empty($request->input('slug')) ? str_slug($request->input('title')) : $request->input('slug');
        $existing = $org->regions()->where('slug', '=', $request->input('slug'))->count();

        if ($existing > 0) {
            return response()->json([ 'error_message' => 'This region already exists', 'errors' => []], 409);
        }

        $region = Region::create([
            'organisation_id' => $org->id,
            'title' => $request->input('title'),
            'slug' => $slug,
        ]);

        if (! empty($request->input('translations'))) {
            foreach ($request->input('translations') as $key => $translation) {
                if (! empty($translation['title'])) {
                    RegionTranslation::create($this->regRepo->mapTranslationInput($region->id, $key, $translation));
                }
            }
        }

        return response()->json($region->fresh('translations'), 201);
    }

    /**
     * @OA\Put(
     *     path="/regions/region/{regionId}",
     *     tags={"Regions"},
     *     summary="Update an existing region",
     *     operationId="updateRegion",
     *     @OA\Parameter(
     *         name="regionId",
     *         in="path",
     *         required=true,
     *         description="ID of the region to update",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Region Title", description="Updated title of the region (optional)"),
     *             @OA\Property(property="slug", type="string", example="updated-region-slug", description="Updated slug for the region (optional)"),
     *             @OA\Property(
     *                 property="translations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="webUrl", type="string", format="url", example="https://example.com", description="Web URL for the translation"),
     *                     @OA\Property(property="lang", type="string", example="en", description="Language code (2 characters)"),
     *                     @OA\Property(property="title", type="string", example="Updated Title in Language", description="Title in the specified language"),
     *                     @OA\Property(property="description", type="string", example="Updated description in language", description="Description in the specified language")
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
    public function updateRegion(Request $request, $regionId)
    {
        $region = Region::find($regionId);

        if (empty($region)) {
            return response()->json([ 'error_message' => 'No region found', 'errors' => []], 404);
        }

        $this->validate($request, [
            'title' => 'sometimes|string',
            'slug' => 'sometimes|string',
            'translations' => 'array',
            'translations.*.webUrl' => 'url',
            'translations.*.lang' => 'alpha|size:2',
            'translations.*.title' => 'string',
            'translations.*.description' => 'string',
        ]);

        $slug = ! empty($request->input('slug')) ? str_slug($request->input('slug')) : $region->slug;

        $region->update([
            'title' => $request->input('title'),
            'slug' => $slug,
        ]);

        if (! empty($request->input('translations'))) {
            foreach ($request->input('translations') as $key => $translation) {
                if (empty($translation['title'])) {
                    continue;
                }

                $existingTrans = RegionTranslation::where('region_id', '=',  $region->id)
                    ->where('language_code', '=', $key)
                    ->first();

                $data = $this->regRepo->mapTranslationInput($region->id, $key, $translation);
                if (empty($existingTrans)) {
                    $existingTrans = RegionTranslation::create($data);
                } else {
                    $existingTrans->update($data);
                }
            }
        }

        return response()->json($region->fresh('translations'), 201);
    }

    /**
     * @OA\Get(
     *     path="/regions/{country_code}",
     *     summary="Get all regions for a specific organisation by country code",
     *     tags={"Regions"},
     *     @OA\Parameter(
     *         name="country_code",
     *         in="path",
     *         description="Country code of the organisation",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     * )
     */
    public function getAllForOrganisation($country_code)
    {
        if (empty($country_code)) {
            return response(null, 404);
        }

        $organisation = Organisation::where('country_code', '=', strtoupper($country_code))->first();

        if (empty($organisation)) {
            return response(null, 404);
        }

        $list = [];
        foreach ($organisation->regions as $region) {
            $data = [
                'id' => $region->id,
                'title' => $region->title,
                'slug' => $region->slug,
                'postsUsedIn' => $region->whatNowEntities()->count(),
                'translations' => [],
            ];
            foreach ($region->translations as $trans) {
                $data['translations'][$trans->language_code] = [
                    'title' => $trans->title,
                    'description' => $trans->description,
                ];
            }
            $list[$region->slug] = $data;
        }

        return response()->json($list, 200);
    }

    /**
     * @OA\Get(
     *     path="/regions/{country_code}/{code}",
     *     summary="Get regions for a specific organisation by country code and language code",
     *     tags={"Regions"},
     *     @OA\Parameter(
     *         name="country_code",
     *         in="path",
     *         description="Country code of the organisation",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Language code for translations",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     * )
     */
    public function getForCountryCode($country_code, $code)
    {
        if (empty($country_code) || empty($code)) {
            return response(null, 404);
        }
        $organisation = Organisation::where('country_code', '=', strtoupper($country_code))->first();

        if (empty($organisation)) {
            return response(null, 404);
        }

        $list = [];
        foreach ($organisation->regions as $region) {
            $translation = $region->translations()
                ->where('language_code', '=', $code)
                ->first();

            $data = [
                'id' => $region->id,
                'slug' => $region->slug,
                'title' => data_get($translation, 'title', ''),
                'description' => data_get($translation, 'description', ''),
            ];

            $list[$region->slug] = $data;
        }

        return response()->json($list, 200);
    }

    /**
     * @OA\Delete(
     *     path="/regions/region/{regionId}",
     *     tags={"Regions"},
     *     summary="Delete a region",
     *     operationId="deleteRegion",
     *     @OA\Parameter(
     *         name="regionId",
     *         in="path",
     *         required=true,
     *         description="ID of the region to delete",
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
    public function deleteRegion($regionId)
    {
        $region = Region::find($regionId);

        if (empty($region)) {
            return response()->json([ 'error_message' => 'No region found', 'errors' => []], 404);
        }

        $keys = $region->translations()->pluck('id')->toArray();
        RegionTranslation::destroy($keys);

        Region::destroy($regionId);

        return response()->json([ 'message' => 'Region deleted'], 202);
    }

    /**
     * @OA\Delete(
     *     path="/regions/region/translation/{translationId}",
     *     tags={"Regions"},
     *     summary="Delete a region translation",
     *     operationId="deleteTranslation",
     *     @OA\Parameter(
     *         name="translationId",
     *         in="path",
     *         required=true,
     *         description="ID of the translation to delete",
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
    public function deleteTranslation($translationId)
    {
        $translation = RegionTranslation::find($translationId);

        if (empty($translation)) {
            return response()->json([ 'error_message' => 'No translation found', 'errors' => []], 404);
        }

        $translation->destroy();

        return response()->json([ 'message' => 'Region deleted'], 202);
    }
}
