<?php namespace Regulus\Identify\Seeder;

use Illuminate\Database\Seeder;

use Auth;
use DB;

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
				'permissions'   => [1],
			],
			[
				'role'          => 'mod',
				'name'          => 'Moderator',
				'access_level'  => 500,
				'display_order' => 2,
			],
			[
				'role'          => 'member',
				'name'          => 'Member',
				'access_level'  => 100,
				'display_order' => 3,
				'default'       => true,
			],
		];

		foreach ($roles as $role)
		{
			$role = array_merge($role, [
				'created_at' => $timestamp,
				'updated_at' => $timestamp,
			]);

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