<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimestampsToTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

		Schema::table('whatnow_entity_translations', function (Blueprint $table) {
			$table->string('web_url', 512)->nullable();
			$table->timestamp('created_at')->nullable();
			$table->timestamp('published_at')->nullable();
		});

		Schema::table('whatnow_entity_stages', function (Blueprint $table) {
			$table->addColumn('integer', 'translation_id', ['unsigned' => true, 'after' => 'id']);
		});

		Schema::table('whatnow_entity_translations', function (Blueprint $table) {
			$table->dropforeign('whatnow_entity_translations_entity_id_foreign');
			$table->dropUnique('whatnow_entity_translations_entity_id_language_code_unique');
			$table->foreign('entity_id')->references('id')->on('whatnow_entities')->onDelete('cascade');
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
