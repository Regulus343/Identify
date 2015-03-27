<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Regulus\Identify\Facade as Auth;

class CreateIdentifyUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('users');

		Schema::create(Auth::getTableName('users'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 120)->unique();
			$table->string('email')->unique();
			$table->string('first_name', 48);
			$table->string('last_name', 48);
			$table->string('password', 60);

			$table->boolean('test')->default(false); // used to filter test users out of a live site without removing them

			/* Optional Fields */

			$table->string('city', 120)->nullable();
			$table->string('region', 100)->nullable(); // province or state
			$table->string('country', 120)->nullable();

			$table->text('about')->nullable();

			$table->boolean('listed')->default(true);
			$table->boolean('listed_email')->nullable();

			/* --------------- */

			$table->integer('access_level')->default(0);

			$table->string('activation_token', 32)->nullable();
			$table->rememberToken();

			$table->timestamps();

			$table->dateTime('activated_at')->nullable();
			$table->dateTime('banned_at')->nullable();
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

		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('email')->unique();
			$table->string('password', 60);
			$table->rememberToken();
			$table->timestamps();
		});
	}

}
