<?php

class UsersTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$table = 'users';

		DB::table(Config::get('identify::tablePrefix').$table)->truncate();

		$defaultPassword   = Hash::make('password');
		$dateCreated       = date('Y-m-d H:i:s');

		$users = array(
			array(
				'username'     => 'Admin',
				'password'     => $defaultPassword,
				'email'        => 'admin@localhost.com',
				'first_name'   => 'Admin',
				'last_name'    => 'Istrator',
				'active'       => true,
				'activated_at' => $dateCreated,
				'created_at'   => $dateCreated,
				'updated_at'   => $dateCreated,
			),
			array(
				'username'     => 'TestUser',
				'password'     => $defaultPassword,
				'email'        => 'test@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Userone',
				'active'       => true,
				'activated_at' => $dateCreated,
				'created_at'   => $dateCreated,
				'updated_at'   => $dateCreated,
				'test'         => true,
			),
			array(
				'username'     => 'TestUser2',
				'password'     => $defaultPassword,
				'email'        => 'test2@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Usertwo',
				'active'       => true,
				'activated_at' => $dateCreated,
				'created_at'   => $dateCreated,
				'updated_at'   => $dateCreated,
				'test'         => true,
			),
			array(
				'username'     => 'TestUser3',
				'password'     => $defaultPassword,
				'email'        => 'test3@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Userthree',
				'active'       => true,
				'activated_at' => $dateCreated,
				'created_at'   => $dateCreated,
				'updated_at'   => $dateCreated,
				'test'         => true,
			),
		);

		foreach ($users as $user) {
			DB::table(Config::get('identify::tablePrefix').$table)->insert($user);
		}
	}

}