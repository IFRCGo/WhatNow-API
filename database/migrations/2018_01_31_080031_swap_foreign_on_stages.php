<?php

	use Carbon\Carbon;
	use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SwapForeignOnStages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('whatnow_entity_stages', function (Blueprint $table) {
			$table->dropforeign('whatnow_entity_stages_entity_id_foreign');
			$table->dropColumn('entity_id');
		});

		Schema::table('whatnow_entity_stages', function (Blueprint $table) {
			$table->index('translation_id');
			$table->foreign('translation_id')->references('id')->on('whatnow_entity_translations')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Up contains destructive changes
    }
}
