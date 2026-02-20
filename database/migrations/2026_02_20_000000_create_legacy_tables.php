<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateLegacyTables extends Migration
{
    /**
     * Run the migrations.
     * Drop all legacy tables in reverse dependency order to respect foreign key constraints.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('legacy_applications', function ($table) {
            $table->increments('id');
            $table->integer('tenant_id');
            $table->string('tenant_user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('key')->unique('legacy_applications_key_unique');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->bigInteger('estimated_users_count')->nullable();
        });
        Schema::table('legacy_applications', function ($table) {
            $table->index(['tenant_id', 'tenant_user_id'], 'legacy_applications_tenant_id_tenant_user_id_index');
        });

        Schema::create('legacy_organisations', function ($table) {
            $table->increments('id');
            $table->string('country_code', 3);
            $table->string('org_name');
            $table->string('oid_code');
            $table->string('attribution_url')->nullable();
            $table->string('attribution_file_name')->nullable();
        });
        Schema::table('legacy_organisations', function ($table) {
            $table->index('country_code', 'legacy_organisations_country_code_index');
        });

        Schema::create('legacy_organisation_details', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('org_id');
            $table->string('language_code', 10)->nullable();
            $table->string('org_name');
            $table->text('attribution_message')->nullable();
            $table->tinyInteger('published');
            $table->unique(['org_id', 'language_code'], 'legacy_organisation_details_org_id_language_code_unique');
            $table->foreign('org_id', 'legacy_organisation_details_org_id_foreign')
                  ->references('id')->on('legacy_organisations')
                  ->onDelete('cascade');
        });

        Schema::create('legacy_region_translations', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('region_id');
            $table->string('language_code', 10)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('region_id', 'legacy_region_translations_region_id_index');
        });

        Schema::create('legacy_regions', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('organisation_id');
            $table->string('title');
            $table->string('slug');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('organisation_id', 'legacy_regions_organisation_id_index');
            $table->index('slug', 'legacy_regions_slug_index');
        });

        Schema::create('legacy_whatnow_entities', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('org_id');
            $table->integer('region_id')->nullable();
            $table->string('event_type');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['org_id', 'event_type', 'region_id'], 'legacy_whatnow_entities_org_id_event_type_region_id_unique');
            $table->index(['org_id', 'event_type'], 'legacy_whatnow_entities_org_id_event_type_index');
            $table->index('region_id', 'legacy_whatnow_entities_region_id_index');
        });

        Schema::create('legacy_whatnow_entity_translations', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->string('language_code', 10)->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('web_url', 512)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreign('entity_id', 'legacy_whatnow_entity_translations_entity_id_foreign')
                  ->references('id')->on('legacy_whatnow_entities')
                  ->onDelete('cascade');
        });

        Schema::create('legacy_whatnow_entity_stages', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('translation_id');
            $table->string('language_code', 10)->nullable();
            $table->enum('stage', ['warning', 'watch', 'immediate', 'recover', 'mitigation', 'seasonalForecast']);
            $table->json('content')->nullable();
            $table->foreign('translation_id', 'legacy_whatnow_entity_stages_translation_id_foreign')
                  ->references('id')->on('legacy_whatnow_entity_translations')
                  ->onDelete('cascade');
            $table->index('translation_id', 'legacy_whatnow_entity_stages_translation_id_index');
        });
    }

    /**
     * Reverse the migrations.
     * Drop all legacy tables in reverse dependency order to respect foreign key constraints.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('legacy_whatnow_entity_stages');
        Schema::dropIfExists('legacy_whatnow_entity_translations');
        Schema::dropIfExists('legacy_whatnow_entities');
        Schema::dropIfExists('legacy_region_translations');
        Schema::dropIfExists('legacy_regions');
        Schema::dropIfExists('legacy_organisation_details');
        Schema::dropIfExists('legacy_organisations');
        Schema::dropIfExists('legacy_applications');

        Schema::enableForeignKeyConstraints();
    }
}


