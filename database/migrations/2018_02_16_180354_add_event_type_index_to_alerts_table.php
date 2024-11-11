<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventTypeIndexToAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('alerts', function (Blueprint $table) {
			$table->index('event');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('alerts', function (Blueprint $table) {
			$table->dropIndex('alerts_event_index');
		});
    }
}
