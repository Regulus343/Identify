<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthApiTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$tableName = Auth::getTableName('api_tokens');

		Schema::create($tableName, function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id');
			$table->string('token', 60);
			$table->timestamps();
			$table->timestamp('expired_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$tableName = Auth::getTableName('api_tokens');

		Schema::dropIfExists($tableName);
	}

}