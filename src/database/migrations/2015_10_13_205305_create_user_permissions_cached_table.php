<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Regulus\Identify\Facade as Auth;

class CreateUserPermissionsCachedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(Auth::getTableName('user_permissions_cached'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id');

			$table->text('permissions')->nullable();

			$table->nullableTimestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(Auth::getTableName('user_permissions_cached'));
	}

}