<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteAttributionData extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
	   \App\Models\OrganisationDetails::truncate();

	   $orgs = \App\Models\Organisation::all();

	   $orgs->each(function(\App\Models\Organisation $org){
			$org->attribution_url = null;
			$org->save();
	   });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}
}
