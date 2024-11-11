<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegionToWhatnowEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatnow_entities', function (Blueprint $table) {
            $table->integer('region_id')->nullable()->after('org_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatnow_entities', function (Blueprint $table) {
            $table->dropColumn('region_id');
        });
    }
}
