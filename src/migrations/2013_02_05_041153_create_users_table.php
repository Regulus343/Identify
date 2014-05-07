<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\Config;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(Config::get('identify::tablePrefix').'users', function($table)
		{
			$table->increments('id');
			$table->string('username', 36);
			$table->string('email');
			$table->string('first_name', 48);
			$table->string('last_name', 48);
			$table->string('password');

			$table->boolean('test'); //used to filter test users out of a live site without removing them

			/* Optional Fields */

			$table->string('city', 76);
			$table->string('region', 100); //province or state
			$table->string('country', 120);

			$table->string('phone', 15);

			$table->string('website');
			$table->string('twitter', 16);
			//$table->string('bitcoin_address', 52);

			$table->text('about');

			$table->boolean('listed');
			$table->boolean('listed_email');

			/* --------------- */

			$table->string('activation_code')->nullable();
			$table->string('reset_password_code')->nullable();
			$table->string('remember_token')->nullable();

			$table->boolean('active');
			$table->dateTime('activated_at');

			$table->boolean('banned');
			$table->dateTime('banned_at');
			$table->text('ban_reason');

			$table->timestamps();

			$table->dateTime('deleted_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(Config::get('identify::tablePrefix').'users');
	}

}