<?php

use Illuminate\Database\Seeder;

use Regulus\Identify\Facade as Auth;

class RolesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$tableRoles           = Auth::getTableName('roles');
		$tableRolePermissions = Auth::getTableName('role_permissions');

		DB::table($tableRoles)->truncate();
		DB::table($tableRolePermissions)->truncate();

		$timestamp = date('Y-m-d H:i:s');

		$roles = [
			[
				'role'          => 'admin',
				'name'          => 'Administrator',
				'access_level'  => 1000,
				'display_order' => 1,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
				'permissions'   => [1],
			],
			[
				'role'          => 'mod',
				'name'          => 'Moderator',
				'access_level'  => 500,
				'display_order' => 2,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			],
			[
				'role'          => 'member',
				'name'          => 'Member',
				'access_level'  => 100,
				'display_order' => 3,
				'default'       => true,
				'created_at'    => $timestamp,
				'updated_at'    => $timestamp,
			],
		];

		foreach ($roles as $role)
		{
			$permissions = isset($role['permissions']) ? $role['permissions'] : [];

			if (isset($role['permissions']))
				unset($role['permissions']);

			$roleId = DB::table($tableRoles)->insertGetId($role);

			foreach ($permissions as $permissionId)
			{
				$rolePermission = [
					'role_id'       => $roleId,
					'permission_id' => $permissionId,
					'created_at'    => $timestamp,
					'updated_at'    => $timestamp,
				];

				DB::table($tableRolePermissions)->insert($rolePermission);
			}
		}
	}

}