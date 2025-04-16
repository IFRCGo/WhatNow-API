<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRegionToSubnational extends Migration
{
    public function up()
    {
        Schema::table('usage_logs', function (Blueprint $table) {
            $table->string('subnational')->nullable();
        });
    
        DB::table('usage_logs')->update([
            'subnational' => DB::raw('region')
        ]);
    
        Schema::table('usage_logs', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }
    
    public function down()
{
    Schema::table('usage_logs', function (Blueprint $table) {
        $table->string('region')->nullable();
    });

    DB::table('usage_logs')->update([
        'region' => DB::raw('subnational')
    ]);

    Schema::table('usage_logs', function (Blueprint $table) {
        $table->dropColumn('subnational');
    });
}
}
