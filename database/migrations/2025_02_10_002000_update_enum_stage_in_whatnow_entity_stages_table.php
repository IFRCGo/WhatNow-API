<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEnumStageInWhatnowEntityStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatnow_entity_stages', function (Blueprint $table) {
            DB::statement("ALTER TABLE whatnow_entity_stages MODIFY COLUMN stage ENUM('immediate', 'warning', 'anticipated', 'assess_and_plan', 'mitigate_risks', 'prepare_to_respond', 'recover') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatnow_entity_stages', function (Blueprint $table) {
            DB::statement("ALTER TABLE whatnow_entity_stages MODIFY COLUMN stage ENUM('immediate', 'warning', 'anticipated', 'assess_and_plan', 'mitigate_risks', 'prepare_to_respond', 'recover') NOT NULL");
        });
    }
}