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

		$users = [
			[
				'username'     => 'Admin',
				'password'     => $defaultPassword,
				'email'        => 'admin@localhost.com',
				'first_name'   => 'Admin',
				'last_name'    => 'Istrator',
				'created_at'   => $dateCreated,
				'updated_at'   => $dateCreated,
				'activated_at' => $dateCreated,
			],
			[
				'username'     => 'TestUser',
				'password'     => $defaultPassword,
				'email'        => 'test@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Userone',
				'created_at'   => $dateCreated,
				'updated_at'   => $dateCreated,
				'activated_at' => $dateCreated,
				'test'         => true,
			],
			[
				'username'     => 'TestUser2',
				'password'     => $defaultPassword,
				'email'        => 'test2@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Usertwo',
				'created_at'   => $dateCreated,
				'updated_at'   => $dateCreated,
				'activated_at' => $dateCreated,
				'test'         => true,
			],
			[
				'username'     => 'TestUser3',
				'password'     => $defaultPassword,
				'email'        => 'test3@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Userthree',
				'created_at'   => $dateCreated,
				'updated_at'   => $dateCreated,
				'activated_at' => $dateCreated,
				'test'         => true,
			],
		];

		foreach ($users as $user) {
			DB::table(Config::get('identify::tablePrefix').$table)->insert($user);
		}
	}

}