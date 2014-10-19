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
		DB::table(Config::get('identify::tablePrefix').'user_roles')->truncate();

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
				'roles'        => [1],
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
				'roles'        => [2],
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
				'roles'        => [3],
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
				'roles'        => [3],
			],
		];

		foreach ($users as $user) {
			$roles = isset($user['roles']) ? $user['roles'] : [];

			if (isset($user['roles']))
				unset($user['roles']);

			$userId = DB::table(Config::get('identify::tablePrefix').$table)->insertGetId($user);

			foreach ($roles as $roleId) {
				$userRole = [
					'user_id'    => $userId,
					'role_id'    => $roleId,
					'created_at' => $dateCreated,
					'updated_at' => $dateCreated,
				];

				DB::table(Config::get('identify::tablePrefix').'user_roles')->insert($userRole);
			}
		}
	}

}