<?php

namespace App\Http\Controllers;

use App\Classes\Repositories\OrganisationRepositoryInterface;
use App\Classes\Transformers\OrganisationTransformer;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;

class OrganisationController extends Controller
{
    /**
     * @var OrganisationRepositoryInterface
     */
    protected $orgRepo;

    /**
     * @var Request
     */
    protected $request;

    protected $manager;

    /**
     * Create a new controller instance.
     *
     * @param OrganisationRepositoryInterface $orgRepo
     * @param Request $request
     * @param Manager $manager
     */
    public function __construct(
        OrganisationRepositoryInterface $orgRepo,
        Request $request,
        Manager $manager
    ) {
        $this->orgRepo = $orgRepo;
        $this->request = $request;
        $this->manager = $manager;
    }

    public function getAll(Request $request)
    {
        try {
            /** @var Collection $orgs */
            $orgs = $this->orgRepo->all();
        } catch (\Exception $e) {
            Log::error('Could not get Organisations list', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Could not get Organisations list',
                'errors' => [],
            ], 500);
        }

        $orgs->each(function (Organisation $org) {
            $org->load('details');
        });

        $resource = new \League\Fractal\Resource\Collection($orgs, new OrganisationTransformer([
            'unpublished' => $request->header('x-api-key') ? false : true,
        ]));
        $response = $this->manager->createData($resource);

        return response()->json($response->toArray(), 200);
    }

    /**
     * @param         $code
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getById($code, Request $request)
    {
        try {
            $org = $this->orgRepo->findByCountryCode($code);
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 404,
                'error_message' => 'Organisation does not exist',
                'errors' => ['No matching organisation for country code'],
            ], 404);
        }

        $org->load('details');

        $resource = new \League\Fractal\Resource\Item($org, new OrganisationTransformer([
            'unpublished' => $request->header('x-api-key') ? false : true,
        ]));
        $response = $this->manager->createData($resource);

        return response()->json($response->toArray(), 200);
    }

    /**
     * @param $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putById($code)
    {
        try {
            $org = $this->orgRepo->findByCountryCode($code);
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 404,
                'error_message' => 'Organisation does not exist',
                'errors' => ['No matching organisation for country code'],
            ], 404);
        }

        $this->validate($this->request, [
            'url' => 'nullable|max:255',
            'translations.*.name' => 'string|max:255',
            'translations.*.attributionMessage' => 'string|max:2048',
        ]);

        try {
            $this->orgRepo->updateDetailsWithInput($org, $this->request->all());
        } catch (\Exception $e) {
            Log::error('Organisation update failed', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Organisation could not be updated',
                'errors' => ['Could not update organisation'],
            ], 500);
        }

        $resource = new \League\Fractal\Resource\Item($org, new OrganisationTransformer([
            'unpublished' => true,
        ]));
        $response = $this->manager->createData($resource);

        return response()->json($response->toArray(), 200);
    }

    /**
     * @deprecated This function used to be used by storm but is not implemented by the new What Now Portal
     *
     * @param $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postImageById($code)
    {
        try {
            $org = $this->orgRepo->findByCountryCode($code);
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 404,
                'error_message' => 'Organisation does not exist',
                'errors' => ['No matching organisation for country code'],
            ], 404);
        }

        if ($this->request->hasFile('image') && $this->request->file('image')->isValid()) {
            $org->attribution_file_name = sprintf('%s_%s.%s', strtolower($org->country_code), uniqid(), $this->request->file('image')->extension());
            $org->save();

            $disk = app()->environment('local', 'testing') ? 'public' : 's3';

            Storage::disk($disk)->put(
                config('app.cdn_asset_path') . $org->getAttributionFilePath(),
                fopen($this->request->file('image'), 'r+'),
                'public'
            );

            return response()->json([
                'status' => 200,
                'message' => 'File uploaded',
                'url' => $org->getAttributionImageUrl(),
            ], 200);
        }

        return response()->json([
            'status' => 500,
            'error_message' => 'Invalid file',
            'errors' => ['Uploaded file is invalid'],
        ], 500);
    }

    /**
     * @deprecated This function used to be used by storm but is not implemented by the new What Now Portal
     *
     * @param $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteImageById($code)
    {
        try {
            $org = $this->orgRepo->findByCountryCode($code);
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 404,
                'error_message' => 'Organisation does not exist',
                'errors' => ['No matching organisation for country code'],
            ], 404);
        }

        $disk = app()->environment('local', 'testing') ? 'public' : 's3';
        Storage::disk($disk)->delete(
            config('app.cdn_asset_path') . $org->getAttributionFilePath()
        );

        $org->attribution_file_name = null;
        $org->save();

        return response()->json([
            'status' => 200,
            'message' => 'File deleted',
        ], 200);
    }
}
