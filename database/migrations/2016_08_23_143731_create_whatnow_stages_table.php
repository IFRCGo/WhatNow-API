<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWhatnowStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('whatnow_entity_stages', function (Blueprint $table) {

			$table->increments('id');
			$table->integer('entity_id')->unsigned();
			$table->string('language_code', 10)->nullable();
			$table->enum('stage', ['midTerm', 'forecast', 'warning', 'watch', 'immediate', 'recover']);
			$table->text('content')->nullable();

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
		Schema::drop('whatnow_entity_stages');
    }
}
