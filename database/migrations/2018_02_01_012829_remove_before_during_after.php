<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveBeforeDuringAfter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('whatnow_entity_translations', function (Blueprint $table) {
			$table->dropColumn('before');
			$table->dropColumn('during');
			$table->dropColumn('after');
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
			$table->text('during')->nullable();
			$table->text('before')->nullable();
			$table->text('after')->nullable();
		});
    }
}
