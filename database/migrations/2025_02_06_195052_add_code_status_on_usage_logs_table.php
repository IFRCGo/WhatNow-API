<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeStatusOnUsageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usage_logs', function (Blueprint $table) {
            $table->integer('code_status')->nullable();
            $table->string('language',10)->nullable();
            $table->string('subnational',45)->nullable();
            $table->string('event_type',45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usage_logs', function (Blueprint $table) {
            $table->dropColumn('code_status');
            $table->dropColumn('language');
            $table->dropColumn('subnational');
            $table->dropColumn('event_type');
        });
    }
}
