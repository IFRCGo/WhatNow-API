<?php

use App\Models\Alert;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AlertTest extends TestCase
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

        return $this->json('POST', '/' . config('app.api_version') . '/alerts', [
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

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_creates_a_new_alert()
    {
        $response = $this->_postAlert();

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'country_code',
            'language_code',
            'event',
            'headline',
            'description',
            'area_polygon',
            'area_description',
            'type',
            'status',
            'scope',
            'category',
            'urgency',
            'severity',
            'certainty',
            'sent_date',
            'onset_date',
            'effective_date',
            'expiry_date',
        ]);
    }

    public function test_alert_exists_in_database()
    {
        $alert = $this->_postAlert();

        $this->assertTrue(is_int($alert->json('id')));
        $this->assertDatabaseHas('alerts', ['id' => $alert->json('id')]);
    }

    public function test_it_generates_a_valid_cap_file()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $path = storage_path('app/public') . '/' . config('app.api_version') . '/alerts/cap12/' . $alert->getXmlPath();

        $this->assertFileExists($path);

        $xml = file_get_contents($path);

        $validator = new \App\Classes\Cap\CapValidator();
        $result = $validator->validate($xml);

        $this->assertTrue($result);
    }

    public function test_it_appears_in_rss_feed()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $response = $this->call('GET', '/' . config('app.api_version') . '/alerts/rss');
        $this->assertEquals(200, $response->status());

        $xml = new SimpleXMLElement($response->content());
        $item = $xml->xpath('//rss/channel/item/guid[text()="' . $alert->getPublicUrl() . '"]');

        $this->assertEquals($alert->getPublicUrl(), (string)$item[0]);
    }

    public function test_it_appears_in_country_feed()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $response = $this->call('GET', '/' . config('app.api_version') . '/org/' . strtolower($alert->organisation->country_code) . '/alerts/rss');
        $this->assertEquals(200, $response->status());

        $xml = new SimpleXMLElement($response->content());
        $item = $xml->xpath('//rss/channel/item/guid[text()="'. $alert->getPublicUrl().'"]');

        $this->assertEquals($alert->getPublicUrl(), (string)$item[0]);
    }

    public function test_it_appears_in_json_feed()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $response = $this->call('GET', '/' . config('app.api_version') . '/alerts');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'identifier' => $alert->getCapIdentifier(),
        ]);
    }

    public function test_it_is_returned_by_identifier()
    {
        $alert = $this->_postAlert();

        $alert = Alert::find($alert->json('id'));

        $response = $this->call('GET', '/' . config('app.api_version') . '/alerts/' . $alert->getCapIdentifier());

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'identifier' => $alert->getCapIdentifier(),
        ]);
    }
}
