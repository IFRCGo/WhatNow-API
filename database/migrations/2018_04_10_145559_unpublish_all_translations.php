<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UnpublishAllTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $trans = \App\Models\WhatNowEntityTranslation::all();

		$trans->each(function(\App\Models\WhatNowEntityTranslation $tran){
			$tran->published_at = null;
			$tran->save();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
