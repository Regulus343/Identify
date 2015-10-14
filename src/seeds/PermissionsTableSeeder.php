<?php

use Illuminate\Database\Seeder;

use Regulus\Identify\Facade as Auth;

class PermissionsTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$table = Auth::getTableName('permissions');

		DB::table($table)->truncate();

		$timestamp = date('Y-m-d H:i:s');

		$permissions = [
			[
				'permission'    => 'admin',
				'name'          => 'Administration',
				'description'   => 'Full administration permissions',
				'display_order' => 1,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			],
		];

		foreach ($permissions as $permission)
		{
			DB::table($table)->insert($permission);
		}
	}

}