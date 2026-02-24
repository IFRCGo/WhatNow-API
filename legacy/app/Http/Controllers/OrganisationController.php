<?php

namespace App\Legacy\Http\Controllers;

use App\Legacy\Classes\Repositories\OrganisationRepositoryInterface;
use App\Legacy\Classes\Transformers\OrganisationTransformer;
use App\Legacy\Models\Organisation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item;

use App\Http\Controllers\Controller;

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

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @param OrganisationRepositoryInterface $orgRepo
     * @param Request $request
     * @param Manager $manager
     */
    public function __construct(
        OrganisationRepositoryInterface $orgRepo,
        Request $request,
        Manager $manager
    ) {
        $this->orgRepo  = $orgRepo;
        $this->request  = $request;
        $this->manager  = $manager;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAll(Request $request)
    {
        try {
            /** @var Collection $orgs */
            $orgs = $this->orgRepo->all();
        } catch (\Exception $e) {
            Log::error('Could not get Organisations list', ['message' => $e->getMessage()]);
            return response()->json([
                'status'        => 500,
                'error_message' => 'Could not get Organisations list',
                'errors'        => [],
            ], 500);
        }

        $orgs->each(function (Organisation $org) {
            $org->load('details');
        });

        $resource = new FractalCollection($orgs, new OrganisationTransformer([
            'unpublished' => $request->header('x-api-key') ? false : true,
        ]));

        $response = $this->manager->createData($resource);
        return response()->json($response->toArray(), 200);
    }

    /**
     * @param string $code
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getById($code, Request $request)
    {
        try {
            /** @var Organisation $org */
            $org = $this->orgRepo->findByCountryCode($code);
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);
            return response()->json([
                'status'        => 404,
                'error_message' => 'Organisation does not exist',
                'errors'        => ['No matching organisation for country code'],
            ], 404);
        }

        $org->load('details');

        $resource = new Item($org, new OrganisationTransformer([
            'unpublished' => $request->header('x-api-key') ? false : true,
        ]));

        $response = $this->manager->createData($resource);
        return response()->json($response->toArray(), 200);
    }
}

