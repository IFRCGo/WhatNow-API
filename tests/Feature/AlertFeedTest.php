<?php

namespace Tests\Feature;

use App\Models\Alert;
use DateInterval;
use DateTime;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use SimpleXMLElement;
use Tests\TestCase;

class AlertFeedTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    /**
     * @return $this
     */
    private function _postAlert($alert = null)
    {
        if (! $alert) {
            $alert = factory(\App\Models\Alert::class)->make();
        }

        return $this->json('POST', '/v1/alerts', [
            'country' => $alert->country_code,
            'language' => $alert->language_code,
            'event' => $alert->event,
            'headline' => $alert->headline,
            'description' => $alert->description,
            'area_polygon' => $alert->area_polygon,
            'area_description' => $alert->area_description,
            'type' => $alert->type,
            'status' => $alert->status,
            'scope' => $alert->scope,
            'category' => $alert->category,
            'urgency' => $alert->urgency,
            'severity' => $alert->severity,
            'certainty' => $alert->certainty,
            'sent_date' => $alert->sent_date->format('c'),
            'onset_date' => $alert->onset_date->format('c'),
            'effective_date' => $alert->effective_date->format('c'),
            'expiry_date' => $alert->expiry_date->format('c'),
        ]);
    }

    public function test_rss_feed_returns_alerts_filtered_by_severity()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $response = $this->call('GET', '/v1/alerts/rss', [
            'severity' => $alert->severity,
        ]);

        dd($response);

        $xml = new SimpleXMLElement($response->content());
        $item = $xml->xpath('//rss/channel/item/guid[text()="' . $alert->getPublicUrl() . '"]');

        $this->assertEquals($alert->getPublicUrl(), (string)$item[0]);

        // Load the xml from disk
        $path = storage_path('app/public/v1/alerts/cap12/') . $alert->getXmlPath();
        $this->assertFileExists($path);

        $cap = simplexml_load_file($path);
        $cap->registerXPathNamespace('c', 'urn:oasis:names:tc:emergency:cap:1.2');
        $capSeverity = $cap->xpath('//c:alert/c:info/c:severity');

        $this->assertEquals(ucfirst($alert->severity), (string)$capSeverity[0]);
    }

    public function test_rss_feed_returns_alerts_filtered_by_event_type()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $response = $this->call(
            'GET',
            '/v1/org/' . strtolower($alert->organisation->country_code) . '/alerts/rss',
            ['eventType' => $alert->event]
        );

        $xml = new SimpleXMLElement($response->content());
        $item = $xml->xpath('//rss/channel/item/title[text()="' . $alert->event . '"]');

        $this->assertEquals($alert->event, (string)$item[0]);
    }

    public function test_json_feed_returns_alerts_filtered_by_event_type()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $response = $this->call(
            'GET',
            '/v1/org/' . strtolower($alert->organisation->country_code) . '/alerts',
            ['eventType' => $alert->event]
        );

        $data = json_decode($response->content(), true);

        $this->assertEquals($alert->event, $data['data'][0]['info']['event']);
    }

    public function test_json_feed_returns_alerts_filtered_by_severity()
    {
        $response = $this->call('GET', '/v1/alerts', [
            'severity' => 'extreme',
        ]);

        $this->assertEquals(200, $response->status());

        $data = json_decode($response->content(), true);

        foreach ($data['data'] as $item) {
            $this->assertEquals('Extreme', $item['info']['severity']);
        }
    }

    public function test_rss_item_link_self_reference_permalink_is_true()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $response = $this->call('GET', '/v1/alerts/rss');

        $xml = new SimpleXMLElement($response->content());
        $item = $xml->xpath('//rss/channel/item[1]/guid/@isPermaLink');

        $this->assertSame('true', (string)$item[0]);
    }

    public function test_active_filter_returns_only_active_alerts()
    {
        $expiryDate = (new DateTime('now'))->sub(new DateInterval('P1D'))->format('c');
        // Expired alert
        $alert1 = factory(\App\Models\Alert::class)->create([
            'org_id' => 1,
            'expiry_date' => $expiryDate,
        ]);

        // Active alert
        $alert2 = factory(\App\Models\Alert::class)->create([
            'org_id' => 2,
        ]);

        $response = $this->call(
            'GET',
            '/v1/alerts/rss',
            ['active' => 'true']
        );

        $xml = new SimpleXMLElement($response->content());

        $expiredItem = $xml->xpath('//rss/channel/item/guid[text()="' . $alert1->getPublicUrl() . '"]');
        $this->assertEmpty($expiredItem);

        $activeItem = $xml->xpath('//rss/channel/item/guid[text()="' . $alert2->getPublicUrl() . '"]');
        $this->assertEquals($alert2->getPublicUrl(), (string)$activeItem[0]);
    }

    public function test_feed_returns_active_and_inactive_alerts_when_active_filter_is_not_applied()
    {
        $expiryDate = (new DateTime('now'))->sub(new DateInterval('P1D'))->format('c');
        // Expired alert
        $alert1 = factory(\App\Models\Alert::class)->create([
            'org_id' => 1,
            'expiry_date' => $expiryDate,
        ]);

        // Active alert
        $alert2 = factory(\App\Models\Alert::class)->create([
            'org_id' => 2,
        ]);

        $response = $this->call(
            'GET',
            '/v1/alerts/rss'
        );

        $xml = new SimpleXMLElement($response->content());

        $expiredItem = $xml->xpath('//rss/channel/item/guid[text()="' . $alert1->getPublicUrl() . '"]');
        $this->assertEquals($alert1->getPublicUrl(), (string)$expiredItem[0]);

        $activeItem = $xml->xpath('//rss/channel/item/guid[text()="' . $alert2->getPublicUrl() . '"]');
        $this->assertEquals($alert2->getPublicUrl(), (string)$activeItem[0]);
    }

    public function test_date_filter_return_alerts_sent_within_specified_range()
    {
        // Create alert 12 hrs in age
        $sentDate = (new DateTime('now'))->sub(new DateInterval('PT12H'))->format('c');
        $alert = factory(\App\Models\Alert::class)->create([
            'org_id' => 1,
            'sent_date' => $sentDate,
        ]);

        // Request last 24 hrs of alerts
        $response = $this->call(
            'GET',
            '/v1/alerts/rss',
            [
                'startTime' => (new DateTime('now'))->sub(new DateInterval('PT24H'))->format('c'),
                'endTime' => (new DateTime('now'))->format('c'),
            ]
        );

        $xml = new SimpleXMLElement($response->content());

        $item = $xml->xpath('//rss/channel/item/guid[text()="' . $alert->getPublicUrl() . '"]');
        $this->assertEquals($alert->getPublicUrl(), (string)$item[0]);
    }

    public function test_date_filter_excludes_alerts_sent_outside_specified_range()
    {
        // Create alert 36 hrs in age
        $sentDate = (new DateTime('now'))->sub(new DateInterval('PT36H'))->format('c');
        $alert = factory(\App\Models\Alert::class)->create([
            'org_id' => 1,
            'sent_date' => $sentDate,
        ]);

        // Request alerts up to 24hrs old
        $response = $this->call(
            'GET',
            '/v1/alerts/rss',
            [
                'startTime' => (new DateTime('now'))->sub(new DateInterval('PT24H'))->format('c'),
                'endTime' => (new DateTime('now'))->format('c'),
            ]
        );

        $xml = new SimpleXMLElement($response->content());

        $item = $xml->xpath('//rss/channel/item/guid[text()="' . $alert->getPublicUrl() . '"]');
        $this->assertEmpty($item);
    }
}
