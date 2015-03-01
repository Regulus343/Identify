<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Regulus\Identify\Facade as Auth;

class CreateRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(Auth::getTableName('roles'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('role');
			$table->string('name');
			$table->text('description')->nullable();
			$table->integer('access_level');
			$table->integer('display_order');
			$table->boolean('default');
			$table->timestamps();
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
		Schema::drop(Auth::getTableName('roles'));
	}

}