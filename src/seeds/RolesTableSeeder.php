<?php

class RolesTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$table = 'roles';

		DB::table(Config::get('identify::tablePrefix').$table)->truncate();
		DB::table(Config::get('identify::tablePrefix').'role_permissions')->truncate();

		$dateCreated = date('Y-m-d H:i:s');

		$roles = [
			[
				'role'           => 'admin',
				'name'           => 'Administrator',
				'display_order'  => 1,
				'created_at'     => $dateCreated,
				'updated_at'     => $dateCreated,
				'permissions'    => [1],
			],
			[
				'role'           => 'mod',
				'name'           => 'Moderator',
				'display_order'  => 2,
				'created_at'     => $dateCreated,
				'updated_at'     => $dateCreated,
			],
			[
				'role'           => 'member',
				'name'           => 'Member',
				'display_order'  => 3,
				'default'        => true,
				'created_at'     => $dateCreated,
				'updated_at'     => $dateCreated,
			],
		];

		foreach ($roles as $role) {
			$permissions = isset($role['permissions']) ? $role['permissions'] : [];

			if (isset($role['permissions']))
				unset($role['permissions']);

			$roleId = DB::table(Config::get('identify::tablePrefix').$table)->insertGetId($role);

			foreach ($permissions as $permissionId) {
				$rolePermission = [
					'role_id'       => $roleId,
					'permission_id' => $permissionId,
					'created_at'    => $dateCreated,
					'updated_at'    => $dateCreated,
				];

				DB::table(Config::get('identify::tablePrefix').'role_permissions')->insert($rolePermission);
			}
		}
	}

}