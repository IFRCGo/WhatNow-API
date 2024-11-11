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
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'seasonalForecast' WHERE stage = 'forecast';");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'mitigation' WHERE stage = 'midTerm';");
        DB::statement("ALTER TABLE whatnow_entity_stages CHANGE COLUMN stage stage ENUM('warning','watch','immediate','recover', 'mitigation', 'seasonalForecast') NOT NULL;");
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
