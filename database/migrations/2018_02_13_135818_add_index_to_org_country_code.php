<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToOrgCountryCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('organisations', function (Blueprint $table) {
			$table->index('country_code');
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
			$table->dropIndex('organisations_country_code_index');
		});
    }
}
