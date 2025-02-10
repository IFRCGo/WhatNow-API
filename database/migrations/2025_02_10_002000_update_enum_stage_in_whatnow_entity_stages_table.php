<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateEnumStageInWhatnowEntityStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE whatnow_entity_stages MODIFY COLUMN stage ENUM('immediate', 'warning', 'anticipated', 'assess_and_plan', 'mitigate_risks', 'prepare_to_respond', 'recover') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //roolback
    }
}