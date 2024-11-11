<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWhatnowTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('whatnow_entities', function (Blueprint $table) {

			$table->increments('id');
			$table->integer('org_id')->unsigned();
			$table->string('event_type');
			$table->string('web_url', 512)->nullable();
			$table->string('attribution_name')->nullable();
			$table->string('attribution_url', 512)->nullable();
			$table->string('attribution_img_url', 512)->nullable();
			$table->string('created_by', 13);
			$table->string('approved_by', 13)->nullable();
			$table->dateTime('approval_date')->nullable();
			$table->timestamps();

			$table->unique(['org_id', 'event_type']);
			$table->foreign('org_id')->references('id')->on('organisations')->onDelete('cascade');
		});

		Schema::create('whatnow_entity_translations', function (Blueprint $table) {

			$table->increments('id');
			$table->integer('entity_id')->unsigned();
			$table->string('language_code', 10)->nullable();
			$table->string('title')->nullable();
			$table->text('description')->nullable();
			$table->text('before')->nullable();
			$table->text('after')->nullable();

			$table->unique(['entity_id', 'language_code']);
			$table->foreign('entity_id')->references('id')->on('whatnow_entities')->onDelete('cascade');
		});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::drop('whatnow_entity_translations');
		Schema::drop('whatnow_entities');

    }
}
