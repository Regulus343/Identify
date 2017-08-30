<?php namespace Regulus\Identify\Seeder;

use Illuminate\Database\Seeder;

use Auth;
use DB;
use Hash;

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
				'username'   => 'Admin',
				'email'      => 'admin@localhost.com',
				'first_name' => 'Admin',
				'last_name'  => 'Istrator',
				'roles'      => [1],
			],
			[
				'username'   => 'TestUser',
				'email'      => 'test@localhost.com',
				'first_name' => 'Test',
				'last_name'  => 'Userone',
				'test'       => true,
				'roles'      => [2],
			],
			[
				'username'   => 'TestUser2',
				'email'      => 'test2@localhost.com',
				'first_name' => 'Test',
				'last_name'  => 'Usertwo',
				'test'       => true,
				'roles'      => [3],
			],
			[
				'username'   => 'TestUser3',
				'email'      => 'test3@localhost.com',
				'first_name' => 'Test',
				'last_name'  => 'Userthree',
				'test'       => true,
				'roles'      => [3],
			],
		];

		foreach ($users as $user)
		{
			$user = array_merge($user, [
				'password'     => $defaultPassword,
				'created_at'   => $timestamp,
				'updated_at'   => $timestamp,
				'activated_at' => $timestamp,
			]);

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