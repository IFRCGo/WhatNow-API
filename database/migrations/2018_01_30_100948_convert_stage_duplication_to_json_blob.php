<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ConvertStageDuplicationToJsonBlob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatnow_entity_stages', function (Blueprint $table) {
            $table->json('contentblob');
        });

        $flattenedStages = new \Illuminate\Database\Eloquent\Collection();

        $uniqueSets = DB::select('select entity_id, language_code, stage from whatnow_entity_stages group by entity_id, language_code, stage');

        foreach ($uniqueSets as $set) {
            /** @var \Illuminate\Database\Eloquent\Builder $dupes */
            $dupes = \App\Models\WhatNowEntityStage::where('entity_id', '=', $set->entity_id)->where('language_code', '=', $set->language_code)->where('stage', '=', $set->stage)->get();

            $flattenedStage = $dupes->first()->toArray();
            unset($flattenedStage['id']);

            $flattenedStage['contentblob'] = json_encode(
                $dupes->pluck('content')->toArray()
            );

            $flattenedStages->push($flattenedStage);
        }

        DB::select('truncate whatnow_entity_stages');

        $flattenedStages->each(function ($stage) {
            \App\Models\WhatNowEntityStage::insert($stage);
        });

        Schema::table('whatnow_entity_stages', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('whatnow_entity_stages', function (Blueprint $table) {
            // renaming table in table with an enum is not supported by doctrine/dbal
            DB::statement('ALTER TABLE whatnow_entity_stages CHANGE contentblob content JSON');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Sorry, data goes one way only.

        Schema::table('whatnow_entity_stages', function (Blueprint $table) {
            $table->dropColumn('content');
        });

        Schema::table('whatnow_entity_stages', function (Blueprint $table) {
            $table->text('content');
        });
    }
}
