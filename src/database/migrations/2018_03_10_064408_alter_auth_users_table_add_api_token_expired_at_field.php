<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAuthUsersTableAddApiTokenExpiredAtField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('auth_users', function(Blueprint $table)
		{
			$table->timestamp('api_token_expired_at')->nullable()->after('api_token');
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
			$table->dropColumn('api_token_expired_at');
		});
	}

}