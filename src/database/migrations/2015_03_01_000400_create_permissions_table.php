<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Regulus\Identify\Facade as Auth;

class CreatePermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(Auth::getTableName('permissions'), function(Blueprint $table)
		{
			$table->increments('id');

			$table->integer('parent_id')->nullable();
			$table->string('permission');
			$table->string('name');
			$table->text('description')->nullable();
			$table->integer('access_level')->default(0);
			$table->integer('display_order');

			$table->nullableTimestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(Auth::getTableName('permissions'));
	}

}
