<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\Config;

class CreatePermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(Config::get('identify::tablePrefix').'permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('parent_id');
			$table->string('permission');
			$table->string('name');
			$table->text('description')->nullable();
			$table->integer('access_level');
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
		Schema::drop(Config::get('identify::tablePrefix').'permissions');
	}

}