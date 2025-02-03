<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportingMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('supporting_messages'); // Drop the table if it already exists

        Schema::create('supporting_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('key_message_id');
            $table->text('content');
            $table->foreign('key_message_id')->references('id')->on('key_messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supporting_messages');
    }
}
