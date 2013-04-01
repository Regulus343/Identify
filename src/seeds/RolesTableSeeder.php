<?php

use Illuminate\Support\Facades\Config;

class RolesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table(Config::get('identify::tablePrefix').'roles')->delete();

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
			Role::create($role);
		}
	}

}