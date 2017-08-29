<?php namespace Regulus\Identify\Seeder;

use Illuminate\Database\Seeder;

use Auth;
use DB;

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
				'description'   => 'Full administrative permissions',
				'display_order' => 1,
			],
		];

		foreach ($permissions as $permission)
		{
			$permission = array_merge($permission, [
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			]);

			DB::table($table)->insert($permission);
		}
	}

}