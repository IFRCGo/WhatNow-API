<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateEntityDataToRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		$whatnows = \App\Models\WhatNowEntity::all();
		$whatnows->each(function(\App\Models\WhatNowEntity $whatnow) {

			/** @var \App\Models\WhatNowEntityTranslation $trans */
			$trans = \App\Models\WhatNowEntityTranslation::where('entity_id', $whatnow->id)->get();

			if ($trans->count() > 0) {
				$trans->each(function(\App\Models\WhatNowEntityTranslation $tran) use ($whatnow) {
					$tran->update([
						'web_url' => $whatnow->web_url,
						'created_at' => new Carbon($whatnow->created_at),
						'published_at' => new Carbon($whatnow->updated_at)
					]);
					$tran->save();
				});
			} else {
				$whatnow->translations()->create([
					'language_code' => 'en',
					'web_url' => $whatnow->web_url,
					'created_at' => new Carbon($whatnow->created_at),
					'published_at' => new Carbon($whatnow->updated_at)
				]);
			}
		});

		Schema::table('whatnow_entities', function (Blueprint $table) {
			$table->dropColumn('web_url');
			$table->dropColumn('attribution_name');
			$table->dropColumn('attribution_url');
			$table->dropColumn('attribution_img_url');
			$table->dropColumn('approved_by');
			$table->dropColumn('created_by');
			$table->dropColumn('approval_date');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('whatnow_entities', function (Blueprint $table) {
			$table->string('attribution_name')->nullable();
			$table->string('attribution_url', 512)->nullable();
			$table->string('attribution_img_url', 512)->nullable();
			$table->string('created_by', 13);
			$table->string('approved_by', 13)->nullable();
			$table->dateTime('approval_date')->nullable();
		});
    }
}
