<?php

use Illuminate\Support\Facades\Config;

class UserRolesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table(Config::get('identify::tablePrefix').'user_roles')->delete();

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
			DB::table(Config::get('identify::tablePrefix').'user_roles')->insert($userRole);
		}
	}

}