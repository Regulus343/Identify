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
		//comment out the second $optionalFields assignment, or alter it as needed
		$optionalFields = array();
		$optionalFields = array('city', 'province', 'country', 'phone', 'website', 'twitter', 'skype');

		Schema::create('users', function($table) use ($optionalFields)
		{
			$table->increments('id');
			$table->string('username');
			$table->string('email');
			$table->string('first_name');
			$table->string('last_name');
			$table->string('password');

			$table->boolean('active');
			$table->dateTime('activated_at');

			$table->boolean('banned');
			$table->dateTime('banned_at');
			$table->text('ban_reason');

			$table->boolean('deleted');
			$table->dateTime('deleted_at');

			$table->boolean('test'); //used to filter test users out of a live site without removing them

			foreach ($optionalFields as $optionalField) {
				$table->string($optionalField);
			}

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