<?php

use App\Classes\Repositories\WhatNowRepository;
use App\Models\Organisation;
use App\Models\OrganisationDetails;
use App\Models\Region;
use App\Models\RegionTranslation;
use App\Models\WhatNowEntity;
use App\Models\WhatNowEntityStage;
use App\Models\WhatNowEntityTranslation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WhatNowTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);

        DB::delete('DELETE FROM alerts');
        DB::delete('DELETE FROM organisation_details');
        DB::delete('DELETE FROM organisations');
    }

    public function test_basic_gets_entity()
    {
        $organisation = factory(Organisation::class)->create();
        factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang = $organisation->details->first()->language_code;

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang,
            'published_at' => Carbon::yesterday()->toDateTimeString(),
            'entity_id' => factory(WhatNowEntity::class)->create([
                'org_id' => $organisation->id,
            ])->id,
        ]);

        $response = $this->call('GET', '/v1/whatnow/' . $translation->entity->id);

        //dd($response->json());

        $response->assertStatus(200);

        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);
    }

    public function test_gets_entity_without_any_published_revisions()
    {
        $organisation = factory(Organisation::class)->create();
        factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $response = $this->call('GET', '/v1/whatnow/' . $entity->id);
        $response->assertStatus(404);
    }

    public function test_gets_latest_published_revision_by_default()
    {
        $organisation = factory(Organisation::class)->create();
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $details2 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'fr',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang1 = $details1->language_code;
        $lang2 = $details2->language_code;

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation2 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation3 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang2,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation4 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang2,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('GET', '/v1/whatnow/'.$entity->id);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);

        $data = json_decode($response->getContent(), true);

        $this->assertSame($translation->title, $data['data']['translations'][$lang1]['title']);
        $this->assertSame($translation->description, $data['data']['translations'][$lang1]['description']);
        $this->assertNotSame($translation2->title, $data['data']['translations'][$lang1]['title']);
        $this->assertNotSame($translation2->description, $data['data']['translations'][$lang1]['description']);

        $this->assertSame($translation3->title, $data['data']['translations'][$lang2]['title']);
        $this->assertSame($translation3->description, $data['data']['translations'][$lang2]['description']);
        $this->assertNotSame($translation4->title, $data['data']['translations'][$lang2]['title']);
        $this->assertNotSame($translation4->description, $data['data']['translations'][$lang2]['description']);
    }

    public function test_gets_latest_draft_revision()
    {
        $organisation = factory(Organisation::class)->create();
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $details2 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'fr',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang1 = $details1->language_code;
        $lang2 = $details2->language_code;

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation2 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation3 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang2,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation4 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang2,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('GET', '/v1/whatnow/' . $entity->id . '/revisions/latest');

        $response->assertStatus(200);

        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);

        $data = json_decode($response->getContent(), true);

        $this->assertSame($translation2->title, $data['data']['translations'][$lang1]['title']);
        $this->assertSame($translation2->description, $data['data']['translations'][$lang1]['description']);
        $this->assertNotSame($translation->title, $data['data']['translations'][$lang1]['title']);
        $this->assertNotSame($translation->description, $data['data']['translations'][$lang1]['description']);

        $this->assertSame($translation4->title, $data['data']['translations'][$lang2]['title']);
        $this->assertSame($translation4->description, $data['data']['translations'][$lang2]['description']);
        $this->assertNotSame($translation3->title, $data['data']['translations'][$lang2]['title']);
        $this->assertNotSame($translation3->description, $data['data']['translations'][$lang2]['description']);
    }

    public function test_gets_latest_draft_revision_even_when_revisions_are_published_in_different_order()
    {
        $organisation = factory(Organisation::class)->create();
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $details2 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'fr',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang1 = $details1->language_code;
        $lang2 = $details2->language_code;

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation2 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation3 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang2,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        sleep(1);

        $translation4 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang2,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('GET', '/v1/whatnow/'.$entity->id);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);

        $data = json_decode($response->getContent(), true);

        $this->assertSame($translation2->title, $data['data']['translations'][$lang1]['title']);
        $this->assertSame($translation2->description, $data['data']['translations'][$lang1]['description']);
        $this->assertNotSame($translation->title, $data['data']['translations'][$lang1]['title']);
        $this->assertNotSame($translation->description, $data['data']['translations'][$lang1]['description']);

        $this->assertSame($translation3->title, $data['data']['translations'][$lang2]['title']);
        $this->assertSame($translation3->description, $data['data']['translations'][$lang2]['description']);
        $this->assertNotSame($translation4->title, $data['data']['translations'][$lang2]['title']);
        $this->assertNotSame($translation4->description, $data['data']['translations'][$lang2]['description']);
    }

    public function test_creates_new_whatnow()
    {
        $organisation = factory(Organisation::class)->create();
        factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang = $organisation->details->first()->language_code;

        $region = factory(Region::class)->create([
            'organisation_id' => $organisation->id,
        ]);

        factory(RegionTranslation::class)->create([
            'region_id' => $region->id,
        ]);

        $response = $this->call('POST', '/v1/whatnow/', [
            'countryCode' => $organisation->country_code,
            'eventType' => 'Example Event',
            'regionName' => $region->title,
            'translations' => [
                'en' => [
                    'lang' => $lang,
                    'webUrl' => 'https://www.google.com',
                    'title' => 'Key Messages for Example Event',
                    'description' => 'These are actions to take to reduce risk and protect you and your household from an example.',
                    'stages' => [
                        'mitigation' => null,
                        'seasonalForecast' => null,
                        'warning' => [
                            'Step 1','Step 2','Step 3',
                        ],
                        'watch' => [
                            'Step 1','Step 2','Step 3',
                        ],
                        'immediate' => [
                            'Step 1','Step 2','Step 3',
                        ],
                        'recover' => null,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);
    }

    public function test_puts_whatnow()
    {
        $organisation = factory(Organisation::class)->create();
        factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang = $organisation->details->first()->language_code;

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang,
            'published_at' => Carbon::yesterday()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        $region = factory(Region::class)->create([
            'organisation_id' => $organisation->id,
        ]);

        factory(RegionTranslation::class)->create([
            'region_id' => $region->id,
        ]);

        $stage = factory(WhatNowEntityStage::class)->create([
            'language_code' => $lang,
            'translation_id' => $translation->id,
        ]);

        $putRequest = [
            'countryCode' => $organisation->country_code,
            'eventType' => $entity->event_type,
            'regionName' => $region->title,
            'translations' => [
                $translation->language_code => [
                    'lang' => $translation->language_code,
                    'webUrl' => $translation->web_url,
                    'title' => $translation->title,
                    'description' => 'this is some updated text',
                    'stages' => [
                        $stage->stage => $stage->content,
                    ],
                ],
            ],
        ];

        sleep(2);

        $response = $this->call('PUT', '/v1/whatnow/'.$entity->id, $putRequest);

        $data = json_decode($response->getContent(), true);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);


        $this->assertSame('this is some updated text', $data['data']['translations'][$translation->language_code]['description']);
    }

    public function test_creates_new_translation_for_existing_entity()
    {
        $organisation = factory(Organisation::class)->create();
        factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang = $organisation->details->first()->language_code;

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang,
            'published_at' => Carbon::yesterday()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        $stage = factory(WhatNowEntityStage::class)->create([
            'language_code' => $lang,
            'translation_id' => $translation->id,
        ]);

        $putRequest = [
            'lang' => $translation->language_code,
            'webUrl' => $translation->web_url,
            'title' => $translation->title,
            'description' => 'this is some updated text',
            'stages' => [
                $stage->stage => $stage->content,
            ],
        ];

        sleep(1);

        $response = $this->call('POST', '/v1/whatnow/'.$entity->id.'/revisions', $putRequest);
        $data = json_decode($response->getContent(), true);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);
    }

    public function test_patch_whatnow_to_publish_unpublished_translation()
    {
        $organisation = factory(Organisation::class)->create();
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang1 = $details1->language_code;

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('PATCH', '/v1/whatnow/'.$entity->id.'/revisions/'.$translation->id, [
            'published' => true,
        ]);

        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);

        $this->assertTrue($data['data']['translations'][$lang1]['published']);

        $this->assertDatabaseHas('whatnow_entity_translations', [
            'id' => $translation->id,
            'entity_id' => $entity->id,
            'published_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function test_patch_whatnow_to_publish_already_published_revision()
    {
        $organisation = factory(Organisation::class)->create();
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang1 = $details1->language_code;

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('PATCH', '/v1/whatnow/'.$entity->id.'/revisions/'.$translation->id, [
            'published' => true,
        ]);

        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);

        $this->assertTrue($data['data']['translations'][$lang1]['published']);

        $this->assertDatabaseHas('whatnow_entity_translations', [
            'id' => $translation->id,
            'entity_id' => $entity->id,
            'published_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function test_patch_whatnow_to_unpublish_published_revision()
    {
        $organisation = factory(Organisation::class)->create();
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $lang1 = $details1->language_code;

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('PATCH', '/v1/whatnow/'.$entity->id.'/revisions/'.$translation->id, [
            'published' => false,
        ]);

        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        $response->assertJsonStructure(['data' => $this->getEntityJsonStructure()]);

        $this->assertFalse($data['data']['translations'][$lang1]['published']);

        $this->assertDatabaseHas('whatnow_entity_translations', [
            'id' => $translation->id,
            'entity_id' => $entity->id,
            'published_at' => null,
        ]);
    }

    public function test_patching_multiple_translations_by_id()
    {
        $organisation = factory(Organisation::class)->create();
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $region = factory(Region::class)->create([
            'organisation_id' => $organisation->id,
        ]);

        factory(RegionTranslation::class)->create([
            'region_id' => $region->id,
        ]);


        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
            'region_id' => $region->id,
        ]);

        $lang1 = $details1->language_code;

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $translation2 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $translation3 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $lang1,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('POST', '/v1/whatnow/publish', [
            'translationIds' => [
                $translation->id,
                $translation2->id,
                $translation3->id,
            ],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('whatnow_entity_translations', [
            'id' => $translation->id,
            'published_at' => Carbon::now()->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('whatnow_entity_translations', [
            'id' => $translation2->id,
            'published_at' => Carbon::now()->toDateTimeString(),
        ]);
        $this->assertDatabaseHas('whatnow_entity_translations', [
            'id' => $translation3->id,
            'published_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function test_deletes_whatnow()
    {
        $organisation = factory(Organisation::class)->create([
            'country_code' => 'USA',
        ]);

        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $details1->language_code,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $response = $this->json('DELETE', '/v1/whatnow/' . $entity->id);
        $response->assertStatus(200);

        $response = $this->json('GET', '/v1/whatnow/' . $entity->id);
        $response->assertStatus(404);

        $this->assertDatabaseMissing('whatnow_entities', [
            'id' => $entity->id,
        ]);

        $this->assertDatabaseMissing('whatnow_entity_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_gets_entities_by_country_code_when_none_published()
    {
        $organisation = factory(Organisation::class)->create([
            'country_code' => 'USA',
        ]);
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $details1->language_code,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('GET', '/v1/org/'.$organisation->country_code.'/whatnow');

        $data = json_decode($response->getContent(), true);

        $response->assertStatus(200);

        $this->assertEmpty($data['data']);
    }

    public function test_gets_entities_returns_only_results_for_correct_country_code()
    {
        $organisation = factory(Organisation::class)->create([
            'country_code' => 'USA',
        ]);
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $organisation2 = factory(Organisation::class)->create([
            'country_code' => 'CAN',
        ]);
        $details2 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation2->id,
            'language_code' => 'fr',
        ]);

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
        ]);

        $entity2 = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation2->id,
        ]);

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $details1->language_code,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        $translation2 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $details2->language_code,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity2->id,
        ]);

        $response = $this->call('GET', '/v1/org/'.$organisation->country_code.'/whatnow');

        $data = json_decode($response->getContent(), true);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['*' => $this->getEntityJsonStructure()]]);
        $this->assertCount(1, $data['data']);
        $this->assertSame((string) $entity->id, $data['data'][0]['id']);
    }

    /** @group new */
    public function test_gets_entities_with_latest_revisions()
    {
        $organisation = factory(Organisation::class)->create([
            'country_code' => 'USA',
        ]);
        $details1 = factory(OrganisationDetails::class)->create([
            'org_id' => $organisation->id,
            'language_code' => 'en',
        ]);

        $region = factory(Region::class)->create([
            'organisation_id' => $organisation->id,
        ]);

        factory(RegionTranslation::class)->create([
            'region_id' => $region->id,
        ]);

        $lang = $organisation->details->first()->language_code;

        $entity = factory(WhatNowEntity::class)->create([
            'org_id' => $organisation->id,
            'region_id' => $region->id,
        ]);

        $translation = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $details1->language_code,
            'published_at' => Carbon::now()->toDateTimeString(),
            'entity_id' => $entity->id,
        ]);

        sleep(2);

        $translation2 = factory(WhatNowEntityTranslation::class)->create([
            'language_code' => $details1->language_code,
            'published_at' => null,
            'entity_id' => $entity->id,
        ]);

        $response = $this->call('GET', '/v1/org/'.$organisation->country_code.'/whatnow/revisions/latest');

        $data = json_decode($response->getContent(), true);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['*' => $this->getEntityJsonStructure()]]);
        $this->assertCount(1, $data['data']);
        $this->assertSame((string) $translation2->id, $data['data'][0]['translations'][$lang]['id']);


        $response = $this->call('GET', '/v1/org/'.$organisation->country_code.'/'.$region->slug.'/whatnow/revisions/latest');
        $data = json_decode($response->getContent(), true);


        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['*' => $this->getEntityJsonStructure()]]);
        $this->assertCount(1, $data['data']);
        $this->assertSame((string) $translation2->id, $data['data'][0]['translations'][$lang]['id']);
    }

    /**
     * @return array
     */
    private function getEntityJsonStructure()
    {
        return [
            'id',
            'countryCode',
            'eventType',
            'regionName',
            'attribution' => [
                'name',
                'countryCode',
                'url',
                'imageUrl',
                'translations' => [
                    '*' => [
                        'languageCode',
                        'name',
                        'attributionMessage',
                    ],
                ],
            ],
            'translations' => [
                '*' => [
                    'id',
                    'lang',
                    'webUrl',
                    'title',
                    'description',
                    'stages' => WhatNowRepository::EVENT_STAGES,
                    'createdAt',
                    'published',
                ],
            ],
        ];
    }
}
