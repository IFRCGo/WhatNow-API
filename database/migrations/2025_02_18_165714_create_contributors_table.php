<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('contributors');
        Schema::create('contributors', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->text('logo');
            $table->integer('org_detail_id')->unsigned();
            $table->foreign('org_detail_id')->references('id')->on('organisation_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contributors');
    }
}
