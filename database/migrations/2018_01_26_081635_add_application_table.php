<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplicationTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('applications', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('tenant_id');
			$table->string('tenant_user_id');
			$table->string('name');
			$table->text('description')->nullable();
			$table->string('key');
			$table->timestamps();
			$table->softDeletes();

			$table->index(['tenant_id', 'tenant_user_id']);
			$table->unique('key');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('applications');
	}
}
