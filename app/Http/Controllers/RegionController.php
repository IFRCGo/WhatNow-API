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
