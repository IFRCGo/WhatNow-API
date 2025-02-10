<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyWhatnowEntityStagesTable extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
public function up()
{
Schema::table('whatnow_entity_stages', function (Blueprint $table) {
$table->dropColumn('content');
});
}

/**
* Reverse the migrations.
*
* @return void
*/
public function down()
{
Schema::table('whatnow_entity_stages', function (Blueprint $table) {
$table->text('content')->nullable();
});
}
}
