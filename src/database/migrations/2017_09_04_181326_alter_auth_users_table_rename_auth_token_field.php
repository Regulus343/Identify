<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAuthUsersTableRenameAuthTokenField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('auth_users', function(Blueprint $table)
		{
			$table->renameColumn('auth_token', 'api_token');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(Blueprint $table)
	{
		Schema::table('auth_users', function(Blueprint $table)
		{
			$table->renameColumn('api_token', 'auth_token');
		});
	}

}