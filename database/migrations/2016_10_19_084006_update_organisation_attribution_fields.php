<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOrganisationAttributionFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('organisations', function (Blueprint $table) {
			$table->string('attribution_url')->nullable()->after('oid_code');
			$table->string('attribution_file_name')->nullable()->after('attribution_url');
		});

		Schema::create('organisation_details', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('org_id')->unsigned();
			$table->string('language_code', 10)->nullable();
			$table->string('org_name');
			$table->text('attribution_message')->nullable();

			$table->unique(['org_id', 'language_code']);
			$table->foreign('org_id')->references('id')->on('organisations')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('organisations', function (Blueprint $table) {
			$table->dropColumn('attribution_url');
			$table->dropColumn('attribution_file_name');
		});

		Schema::drop('organisation_details');
    }
}
