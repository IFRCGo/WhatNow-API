<?php

namespace App\Http\Controllers;

use App\Classes\Feeds\GdpcAlertFeed;
use App\Classes\Repositories\AlertRepositoryInterface;
use App\Classes\Repositories\OrganisationRepositoryInterface;
use App\Classes\Transformers\AlertTransformer;
use App\Jobs\GenerateCapAlertJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class AlertController extends Controller
{
    /**
     * @var AlertRepositoryInterface
     */
    protected $alertRepo;

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
    protected $responseManager;

    /**
     * AlertController constructor.
     *
     * @param AlertRepositoryInterface $alertRepo
     * @param OrganisationRepositoryInterface $orgRepo
     * @param Request $request
     * @param Manager $responseManager
     */
    public function __construct(
        AlertRepositoryInterface $alertRepo,
        OrganisationRepositoryInterface $orgRepo,
        Request $request,
        Manager $responseManager
    ) {
        $this->alertRepo = $alertRepo;
        $this->orgRepo = $orgRepo;
        $this->request = $request;
        $this->responseManager = $responseManager;
    }

    public function post()
    {
        try {
            $org = $this->orgRepo->findByCountryCode($this->request->input('country'));
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 500,
                'error_message' => 'Unable to create alert',
                'errors' => ['No matching organisation for country code'],
            ], 500);
        }

        $data = [
            'org_id' => $org->id,
            'country_code' => $this->request->input('country'),
            'language_code' => $this->request->input('language'),
            'event' => $this->request->input('event'),
            'headline' => $this->request->input('headline'),
            'description' => $this->request->input('description'),
            'area_polygon' => $this->request->input('area_polygon'),
            'area_description' => $this->request->input('area_description'),
            'type' => $this->request->input('type'),
            'status' => $this->request->input('status'),
            'scope' => $this->request->input('scope'),
            'category' => $this->request->input('category'),
            'urgency' => $this->request->input('urgency'),
            'severity' => $this->request->input('severity'),
            'certainty' => $this->request->input('certainty'),
            'sent_date' => $this->request->input('sent_date'),
            'onset_date' => $this->request->input('onset_date'),
            'effective_date' => $this->request->input('effective_date'),
            'expiry_date' => $this->request->input('expiry_date'),
        ];

        $alert = $this->alertRepo->newInstance();

        if (! $alert->validate($data)) {
            return response()->json([
                'status' => 500,
                'error_message' => 'Unable to create alert',
                'errors' => $alert->errors(),
            ], 500);
        }

        $newAlert = $this->alertRepo->create($data);

        $this->dispatch(new GenerateCapAlertJob($newAlert->id));

        return response()->json($newAlert, 201);
    }

    public function parseFeedParams(GdpcAlertFeed $feed)
    {
        $this->validate($this->request, [
            'eventType' => 'string',
            'severity' => 'string',
            'active' => 'in:true',
            'startTime' => 'date',
            'endTime' => 'date',
        ]);

        $feed->setBaseUrl(config('app.url'));
        $feed->setEventTypeFilter($this->request->query('eventType', null));
        $feed->setSeverityFilter($this->request->query('severity', null));
        $feed->setActiveOnlyFilter($this->request->query('active', false));
        $feed->setStartTimeFilter($this->request->query('startTime', null));
        $feed->setEndTimeFilter($this->request->query('endTime', null));
    }

    public function getRss(GdpcAlertFeed $feed)
    {
        $this->parseFeedParams($feed);
        $xml = $feed->buildRss();

        return response($xml, 200)->header('Content-Type', 'application/rss+xml');
    }

    public function get(GdpcAlertFeed $feed)
    {
        $this->parseFeedParams($feed);

        if (in_array('application/application/rss+xml', $this->request->getAcceptableContentTypes())) {
            $xml = $feed->buildRss();

            return response($xml, 200)->header('Content-Type', 'application/rss+xml');
        }

        return response()->json($feed->getResponseData(), 200);
    }

    public function getRssByOrg(GdpcAlertFeed $feed, $code)
    {
        $this->parseFeedParams($feed);

        try {
            $org = $this->orgRepo->findByCountryCode(strtoupper($code));
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response(null, 404);
        }

        $feed->title = $org->org_name . ' Alert Feed';
        $feed->description = 'Alerts from the ' . $org->org_name;
        $feed->setCountry(strtoupper($org->country_code));

        $xml = $feed->buildRss();

        return response($xml, 200)->header('Content-Type', 'application/rss+xml');
    }

    public function getByOrg(GdpcAlertFeed $feed, $code)
    {
        $this->parseFeedParams($feed);

        try {
            $org = $this->orgRepo->findByCountryCode(strtoupper($code));
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response(null, 404);
        }

        $feed->title = $org->org_name . ' Alert Feed';
        $feed->description = 'Alerts from the ' . $org->org_name;
        $feed->setCountry(strtoupper($org->country_code));

        if (in_array('application/rss+xml', $this->request->getAcceptableContentTypes())) {
            return $this->getRssByOrg($feed, $code);
        }

        return response()->json($feed->getResponseData(), 200);
    }

    public function getByIdentifier($identifier)
    {
        try {
            $alert = $this->alertRepo->getByIdentifier($identifier);
        } catch (\Exception $e) {
            Log::error('Organisation not found', ['message' => $e->getMessage()]);

            return response(null, 404);
        }

        $resource = new Item($alert, new AlertTransformer);
        $response = $this->responseManager->createData($resource);

        return response()->json($response->toArray(), 200);
    }
}
