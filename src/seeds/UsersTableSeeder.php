<?php

use Illuminate\Support\Facades\Config;

class UsersTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table(Config::get('identify::tablePrefix').'users')->delete();

		$defaultPassword = Hash::make('password');
		$dateActivated     = date('Y-m-d H:i:s');

		$users = array(
			array(
				'username'     => 'Admin',
				'password'     => $defaultPassword,
				'email'        => 'admin@localhost',
				'first_name'   => 'Admin',
				'last_name'    => 'Istrator',
				'active'       => true,
				'activated_at' => $dateActivated,
			),

			array(
				'username'     => 'TestUser',
				'password'     => $defaultPassword,
				'email'        => 'test@localhost',
				'first_name'   => 'Test',
				'last_name'    => 'Userone',
				'active'       => true,
				'activated_at' => $dateActivated,
				'test'         => true,
			),

			array(
				'username'     => 'TestUser2',
				'password'     => $defaultPassword,
				'email'        => 'test2@localhost',
				'first_name'   => 'Test',
				'last_name'    => 'Usertwo',
				'active'       => true,
				'activated_at' => $dateActivated,
				'test'         => true,
			),

			array(
				'username'     => 'TestUser2',
				'password'     => $defaultPassword,
				'email'        => 'test3@localhost',
				'first_name'   => 'Test',
				'last_name'    => 'Userthree',
				'active'       => true,
				'activated_at' => $dateActivated,
				'test'         => true,
			),
		);

		foreach ($users as $user) {
			Regulus\Identify\User::create($user);
		}
	}

}