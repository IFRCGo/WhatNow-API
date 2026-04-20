<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeMitigateRisksToMitigateRiskInWhatnowEntityStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE whatnow_entity_stages MODIFY COLUMN stage ENUM('immediate', 'warning', 'anticipated', 'assess_and_plan', 'mitigate_risks', 'mitigate_risk', 'prepare_to_respond', 'recover') NOT NULL");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'mitigate_risk' WHERE stage = 'mitigate_risks'");
        DB::statement("ALTER TABLE whatnow_entity_stages MODIFY COLUMN stage ENUM('immediate', 'warning', 'anticipated', 'assess_and_plan', 'mitigate_risk', 'prepare_to_respond', 'recover') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE whatnow_entity_stages MODIFY COLUMN stage ENUM('immediate', 'warning', 'anticipated', 'assess_and_plan', 'mitigate_risks', 'mitigate_risk', 'prepare_to_respond', 'recover') NOT NULL");
        DB::statement("UPDATE whatnow_entity_stages SET stage = 'mitigate_risks' WHERE stage = 'mitigate_risk'");
        DB::statement("ALTER TABLE whatnow_entity_stages MODIFY COLUMN stage ENUM('immediate', 'warning', 'anticipated', 'assess_and_plan', 'mitigate_risks', 'prepare_to_respond', 'recover') NOT NULL");
    }
}

