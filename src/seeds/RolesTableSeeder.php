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

		$roles = array(
			array(
				'role' => 'admin',
				'name' => 'Administrator',
			),
			array(
				'role' => 'mod',
				'name' => 'Moderator',
			),
			array(
				'role' => 'member',
				'name' => 'Member',
			),
		);

		foreach ($roles as $role) {
			DB::table(Config::get('identify::tablePrefix').$table)->insert($role);
		}
	}

}