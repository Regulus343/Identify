<?php

class RolesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$table = 'roles';

		DB::table(Config::get('identify::tablePrefix').$table)->truncate();

		$dateCreated = date('Y-m-d H:i:s');

		$roles = [
			[
				'role'           => 'admin',
				'name'           => 'Administrator',
				'display_order'  => 1,
				'created_at'     => $dateCreated,
				'updated_at'     => $dateCreated,
			],
			[
				'role'           => 'mod',
				'name'           => 'Moderator',
				'display_order'  => 2,
				'created_at'     => $dateCreated,
				'updated_at'     => $dateCreated,
			],
			[
				'role'           => 'member',
				'name'           => 'Member',
				'display_order'  => 3,
				'default'        => true,
				'created_at'     => $dateCreated,
				'updated_at'     => $dateCreated,
			],
		];

		foreach ($roles as $role) {
			DB::table(Config::get('identify::tablePrefix').$table)->insert($role);
		}
	}

}