<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStageInWhatnowEntityStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE whatnow_entity_stages CHANGE COLUMN stage stage ENUM('midTerm','forecast','warning','watch','immediate','recover', 'mitigation', 'seasonalForecast') NOT NULL;");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'anticipated' WHERE stage = 'seasonalForecast';");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'anticipated' WHERE stage = 'forecast';");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'immediate' WHERE stage = 'midTerm';");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'assess_and_plan' WHERE stage = 'watch';");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'mitigate_risks' WHERE stage = 'mitigation';");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'prepare_to_respond' WHERE stage = 'watch';");
        DB::statement("ALTER TABLE whatnow_entity_stages CHANGE COLUMN stage stage ENUM('immediate','warning','anticipated','assess_and_plan','mitigate_risks','prepare_to_respond','recover') NOT NULL;");
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
