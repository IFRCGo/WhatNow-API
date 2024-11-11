<?php

	use Carbon\Carbon;
	use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertStagesToBeRelatedToTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		/** @var \App\Models\WhatNowEntityTranslation $trans */
		$trans = \App\Models\WhatNowEntityTranslation::all();

		$trans->each(function(\App\Models\WhatNowEntityTranslation $translation){

			$stages = \App\Models\WhatNowEntityStage::where('entity_id', $translation->entity_id)->where('language_code', $translation->language_code)->get();

			$stages->each(function(\App\Models\WhatNowEntityStage $stage) use ($translation) {
				$stage->translation()->associate($translation);
				$stage->save();
			});
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
