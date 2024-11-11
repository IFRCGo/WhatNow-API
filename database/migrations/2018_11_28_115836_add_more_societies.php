<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreSocieties extends Migration
{
	public function up()
	{
		$table = DB::table("organisations");
		$organisations = [
			[
				"country_code" => "TUV",
				"org_name" => "Tuvalu Red Cross Society",
				"oid_code" => "urn:oid:2.49.0.4.189",
				"attribution_url" => null,
				"attribution_file_name" => null
			],
			[
				"country_code" => "HKG",
				"org_name" => "Hong Kong Red Cross",
				"oid_code" => "urn:oid:2.49.0.4.190",
				"attribution_url" => null,
				"attribution_file_name" => null
			],
			[
				"country_code" => "TWN",
				"org_name" => "Taiwan Red Cross",
				"oid_code" => "urn:oid:2.49.0.4.191",
				"attribution_url" => null,
				"attribution_file_name" => null
			],
			[
				"country_code" => "MAC",
				"org_name" => "Macau Red Cross",
				"oid_code" => "urn:oid:2.49.0.4.192",
				"attribution_url" => null,
				"attribution_file_name" => null
			]
		];
		foreach ($organisations as $organisation) {
			$table->insert($organisation);
		}
	}

	public function down()
	{
		$table = DB::table("organisations");
		$table->whereIn("country_code", ["TUV", "HKG", "TWN", "MAC"])->delete();
	}
}
