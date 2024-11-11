<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('org_id')->unsigned();
            $table->string('country_code', 10)->index();
            $table->string('language_code', 10)->nullable();

            $table->string('event', 120);
            $table->string('headline', 512);
            $table->text('description')->nullable();
            $table->text('area_polygon')->nullable();
            $table->string('area_description', 512);

            $table->enum('type', ['alert', 'update', 'cancel', 'ack', 'error']);
            $table->enum('status', ['actual', 'system', 'test', 'draft']);
            $table->enum('scope', ['public', 'restricted', 'private']);

            $table->enum('category', ['geo', 'met', 'safety', 'security', 'rescue', 'fire', 'health', 'env', 'transport', 'infra', 'CBRNE', 'other']);
            $table->enum('urgency', ['immediate', 'expected', 'future', 'past', 'unknown']);
            $table->enum('severity', ['extreme', 'severe', 'moderate', 'minor', 'unknown']);
            $table->enum('certainty', ['observed', 'likely', 'possible', 'unlikely', 'unknown']);

            $table->dateTime('sent_date')->index();
            $table->dateTime('onset_date')->nullable();
            $table->dateTime('effective_date')->nullable();
            $table->dateTime('expiry_date')->nullable();

            $table->timestamps();

            $table->foreign('org_id')->references('id')->on('organisations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alerts');
    }
}
