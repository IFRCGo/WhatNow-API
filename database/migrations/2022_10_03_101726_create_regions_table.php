<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organisation_id')->unsigned()->index();
            $table->string('title');
            $table->string('slug')->index();

            $table->timestamps();
        });


        Schema::create('region_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('region_id')->unsigned()->index();
            $table->string('language_code', 10)->nullable();
            $table->string('title');
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('regions');
        Schema::drop('region_translations');
    }
}
