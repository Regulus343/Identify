<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\Config;

class CreateRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(Config::get('identify::tablePrefix').'roles', function($table)
		{
			$table->increments('id');
			$table->string('role');
			$table->string('name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(Config::get('identify::tablePrefix').'roles');
	}

}