<?php

class RolesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table(Config::get('identify::tablePrefix').'roles')->truncate();

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
			Regulus\Identify\Role::create($role);
		}
	}

}