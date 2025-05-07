<?php

use App\Models\Organisation;
use App\Models\OrganisationDetails;
use App\Models\RegionTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegionTest extends TestCase
{
    use DatabaseTransactions;

    protected Organisation $organisation;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);

        $this->organisation = factory(Organisation::class)->create();

        factory(OrganisationDetails::class)->create([
            'org_id' => $this->organisation->id,
        ]);

        $this->delete('DELETE FROM subnationals');

        $this->delete('DELETE FROM region_translations');
    }

    public function testCreateRegionForOrganisation()
    {
        $data = [
            'countryCode' => $this->organisation->country_code,
            'title' => 'Test Region 1',
            'slug' => 'test-subnational-1',
            'translations' => [
                'en' => [
                    'title' => 'Testing 1',
                    'description' => 'testing 123',
                ],
                'es' => [
                    'title' => 'Prueba 1',
                    'description' => 'prueba 123',
                ],
            ],
        ];

        $response = $this->json('POST', '/' . config('app.api_version') .  '/subnationals', $data);

        //dd($response);

        $response->assertStatus(201);
    }

    public function testUpdateRegion()
    {
        $region = $this->organisation->subnationals()->create([
            'countryCode' => $this->organisation->country_code,
            'title' => 'Test Region',
            'slug' => 'test-subnational',
            'translations' => [
                'en' => [
                    'title' => 'Testing 1',
                    'description' => 'testing 123',
                ],
                'es' => [
                    'title' => 'Prueba 1',
                    'description' => 'prueba 123',
                ],
            ],
        ]);

        $data = [
            'countryCode' => $this->organisation->country_code,
            'title' => 'Test Region 1',
            'slug' => 'test-subnational-1',
            'translations' => [
                'en' => [
                    'title' => 'Testing 2',
                    'description' => 'testing 456',
                ],
                'es' => [
                    'title' => 'Prueba 2',
                    'description' => 'prueba 456',
                ],
                'fr' => [
                    'title' => 'Test 2',
                    'description' => 'test 456',
                ],
            ],
        ];

        $matched = RegionTranslation::where('region_id', '=', 1)
            ->where('language_code', '=', 'en')
            ->where('title', '=',  'Testing 2')
            ->count();

        $this->assertEquals(0, $matched);

        $this->json('PUT', '/' . config('app.api_version') .  '/subnationals/subnational/{$region->id}', $data)
            ->assertStatus(201);

        $matched = RegionTranslation::where('region_id', '=', $region->id)
            ->where('language_code', '=', 'en')
            ->where('title', '=',  'Testing 2')
            ->count();

        $this->assertEquals(1, $matched);
    }

    public function testGetRegionsForOrganisation()
    {
        $this->json('GET', '/' . config('app.api_version') .  '/subnationals/USA')
            ->assertStatus(200);
    }

    public function testGetLaguageSpecificRegionsForOrganisation()
    {
        $this->json('GET', '/' . config('app.api_version') .  '/subnationals/USA/es')
            ->assertStatus(200);
    }

    public function testDeleteRegionForOrganisation()
    {
        $region = $this->organisation->subnationals()->create([
            'countryCode' => $this->organisation->country_code,
            'title' => 'Test Region',
            'slug' => 'test-subnational',
            'translations' => [
                'en' => [
                    'title' => 'Testing 1',
                    'description' => 'testing 123',
                ],
                'es' => [
                    'title' => 'Prueba 1',
                    'description' => 'prueba 123',
                ],
            ],
        ]);

        $this->json('DELETE', '/' . config('app.api_version') .  '/subnationals/subnational/{$region->id}')
            ->assertStatus(202);
    }
}
