<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table)
		{
			$table->increments('id');
			$table->string('username', 36);
			$table->string('email');
			$table->string('first_name', 48);
			$table->string('last_name', 48);
			$table->string('password');

			$table->boolean('active');
			$table->dateTime('activated_at');

			$table->boolean('banned');
			$table->dateTime('banned_at');
			$table->text('ban_reason');

			$table->boolean('deleted');
			$table->dateTime('deleted_at');

			$table->boolean('test'); //used to filter test users out of a live site without removing them

			/* Optional Fields */

			$table->string('city', 76);
			$table->string('province', 96);
			$table->string('country', 96);
			$table->string('phone', 15);
			$table->string('website');
			$table->string('twitter', 16);

			$table->boolean('listed');
			$table->boolean('listed_email');

			/* --------------- */

			$table->string('activation_code');
			$table->string('forgot_password_code');

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}