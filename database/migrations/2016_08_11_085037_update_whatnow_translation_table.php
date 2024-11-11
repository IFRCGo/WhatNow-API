<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateWhatnowTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatnow_entity_translations', function (Blueprint $table) {
			$table->text('during')->nullable()->after('before');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatnow_entity_translations', function (Blueprint $table) {
			$table->dropColumn('during');
        });
    }
}
