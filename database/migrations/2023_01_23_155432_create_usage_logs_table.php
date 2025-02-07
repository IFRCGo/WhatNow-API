<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('application_id');
            $table->string('method');
            $table->string('endpoint');
            $table->timestamp('timestamp');
        });
    }
}
