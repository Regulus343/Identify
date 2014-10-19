<?php

class PermissionsTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$table = 'permissions';

		DB::table(Config::get('identify::tablePrefix').$table)->truncate();

		$dateCreated = date('Y-m-d H:i:s');

		$permissions = [
			[
				'permission'     => 'admin',
				'name'           => 'Administration',
				'display_order'  => 1,
				'created_at'     => $dateCreated,
				'updated_at'     => $dateCreated,
			],
		];

		foreach ($permissions as $permission) {
			DB::table(Config::get('identify::tablePrefix').$table)->insert($permission);
		}
	}

}