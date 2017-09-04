<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Regulus\Identify\Facade as Auth;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$tableName = Auth::getTableName('users');

		Schema::create($tableName, function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('username', 132);
			$table->string('email');
			$table->string('first_name', 76);
			$table->string('last_name', 76);
			$table->string('password');

			$table->boolean('test')->default(false); // used to filter test users out of a live site without removing them

			$table->integer('access_level')->default(0);

			$table->string('auth_token', 128)->nullable();
			$table->string('activation_token', 32)->nullable();
			$table->rememberToken();

			$table->nullableTimestamps();

			$table->timestamp('activated_at')->nullable();
			$table->timestamp('last_logged_in_at')->nullable();
			$table->timestamp('password_changed_at')->nullable();
			$table->timestamp('banned_at')->nullable();
			$table->text('ban_reason')->nullable();

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
		Schema::drop(Auth::getTableName('users'));
	}

}