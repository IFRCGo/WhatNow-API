<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameRegionsToSubnational extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('regions') && ! Schema::hasTable('subnationals')) {
            Schema::rename('regions', 'subnationals');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('subnationals') && ! Schema::hasTable('regions')) {
            Schema::rename('subnationals', 'regions');
        }
    }
}
