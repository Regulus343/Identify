<?php

use Illuminate\Database\Seeder;

use Regulus\Identify\Facade as Auth;

class UsersTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$tableUsers     = Auth::getTableName('users');
		$tableUserRoles = Auth::getTableName('user_roles');

		DB::table($tableUsers)->truncate();
		DB::table($tableUserRoles)->truncate();

		$defaultPassword = Hash::make('password');
		$timestamp       = date('Y-m-d H:i:s');

		$users = [
			[
				'name'         => 'Admin',
				'password'     => $defaultPassword,
				'email'        => 'admin@localhost.com',
				'first_name'   => 'Admin',
				'last_name'    => 'Istrator',
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,
				'activated_at' => $timestamp,
				'roles'        => [1],
			],
			[
				'name'         => 'TestUser',
				'password'     => $defaultPassword,
				'email'        => 'test@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Userone',
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,
				'activated_at' => $timestamp,
				'test'         => true,
				'roles'        => [2],
			],
			[
				'name'         => 'TestUser2',
				'password'     => $defaultPassword,
				'email'        => 'test2@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Usertwo',
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,
				'activated_at' => $timestamp,
				'test'         => true,
				'roles'        => [3],
			],
			[
				'name'         => 'TestUser3',
				'password'     => $defaultPassword,
				'email'        => 'test3@localhost.com',
				'first_name'   => 'Test',
				'last_name'    => 'Userthree',
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,
				'activated_at' => $timestamp,
				'test'         => true,
				'roles'        => [3],
			],
		];

		foreach ($users as $user)
		{
			$roles = isset($user['roles']) ? $user['roles'] : [];

			if (isset($user['roles']))
				unset($user['roles']);

			$userId = DB::table($tableUsers)->insertGetId($user);

			foreach ($roles as $roleId)
			{
				$userRole = [
					'user_id'    => $userId,
					'role_id'    => $roleId,
					'created_at' => $timestamp,
					'updated_at' => $timestamp,
				];

				DB::table($tableUserRoles)->insert($userRole);
			}
		}
	}

}