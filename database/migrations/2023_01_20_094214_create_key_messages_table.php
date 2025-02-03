<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeyMessagesTable extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
public function up()
{
Schema::dropIfExists('key_messages'); // Drop the table if it already exists

Schema::create('key_messages', function (Blueprint $table) {
$table->increments('id');
$table->unsignedInteger('entities_stage_id');
$table->string('title');
$table->foreign('entities_stage_id')->references('id')->on('whatnow_entity_stages')->onDelete('cascade');
});
}

/**
* Reverse the migrations.
*
* @return void
*/
public function down()
{
Schema::dropIfExists('key_messages');
}
}
