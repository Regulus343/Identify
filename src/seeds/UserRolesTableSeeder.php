<?php

class UserRolesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$table = 'user_roles';

		DB::table(Config::get('identify::tablePrefix').$table)->truncate();

		$userRoles = array(
			array(
				'user_id' => 1,
				'role_id' => 1,
			),
			array(
				'user_id' => 2,
				'role_id' => 2,
			),
			array(
				'user_id' => 3,
				'role_id' => 3,
			),
			array(
				'user_id' => 4,
				'role_id' => 3,
			),
		);

		foreach ($userRoles as $userRole) {
			DB::table(Config::get('identify::tablePrefix').$table)->insert($userRole);
		}
	}

}