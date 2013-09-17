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

		$roles = array(
			array(
				'role'       => 'admin',
				'name'       => 'Administrator',
				'created_at' => $dateCreated,
				'updated_at' => $dateCreated,
			),
			array(
				'role'       => 'mod',
				'name'       => 'Moderator',
				'created_at' => $dateCreated,
				'updated_at' => $dateCreated,
			),
			array(
				'role'       => 'member',
				'name'       => 'Member',
				'created_at' => $dateCreated,
				'updated_at' => $dateCreated,
			),
		);

		foreach ($roles as $role) {
			DB::table(Config::get('identify::tablePrefix').$table)->insert($role);
		}
	}

}