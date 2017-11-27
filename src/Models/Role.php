<?php namespace Regulus\Identify\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Auth;

class Role extends Model {

	use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'roles';

	/**
	 * The attributes that cannot be updated.
	 *
	 * @var array
	 */
	protected $guarded = ['id'];

	/**
	 * Enable soft delete for the model.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'default' => 'boolean',
	];

	/**
	 * The permissions array for the role.
	 *
	 * @var    array
	 */
	public $permissions = [];

	/**
	 * The constructor which adds the table prefix from the config settings.
	 *
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$this->table = Auth::getTableName($this->table);
	}

	/**
	 * The users of the role.
	 *
	 * @return Collection
	 */
	public function users()
	{
		return $this->belongsToMany(config('auth.model'), Auth::getTableName('user_roles'))
			->orderBy('username');
	}

	/**
	 * The permissions of the role.
	 *
	 * @return Collection
	 */
	public function rolePermissions()
	{
		return $this->belongsToMany('Regulus\Identify\Models\Permission', Auth::getTableName('role_permissions'))
			->orderBy('display_order')
			->orderBy('name');
	}

	/**
	 * Get a select box list of roles.
	 *
	 * @param  mixed    $select
	 * @return array
	 */
	public static function getSelectable($select = null)
	{
		if (is_null($select) || !is_array($select) || count($select) == 0)
			$select = ['role', 'name'];

		if (count($select) == 1)
			$select[1] = $select[0];

		$roles   = static::orderBy('display_order')->get();
		$options = [];

		foreach ($roles as $role) {
			$options[$role->{$select[0]}] = $role->{$select[1]};
		}

		return $options;
	}

	/**
	 * Get the permissions of the role.
	 *
	 * @param  string   $field
	 * @return array
	 */
	public function getPermissions($field = 'permission')
	{
		if (empty($this->permissions))
		{
			$this->permissions = [];

			// get role derived permissions
			foreach ($this->rolePermissions as $permission)
			{
				if (!in_array($permission->{$field}, $this->permissions))
					$this->permissions[] = $permission->{$field};
			}

			// get access level derived permissions
			if (config('auth.enable_access_level'))
			{
				$permissions = Permission::where('access_level', '<=', $this->access_level)->get();
				foreach ($permissions as $permission)
				{
					if (!in_array($permission->{$field}, $this->permissions))
						$this->permissions[] = $permission->{$field};
				}
			}

			asort($this->permissions);
		}

		return $this->permissions;
	}

	/**
	 * Get the permission names of the role.
	 *
	 * @return array
	 */
	public function getPermissionNames()
	{
		return $this->getPermissions('name');
	}

	/**
	 * Check if a role has a particular permission.
	 *
	 * @param  mixed    $permissions
	 * @return boolean
	 */
	public function hasPermission($permissions)
	{
		$permissions = Auth::formatPermissionsArray($permissions);

		if (empty($permissions))
			return true;

		foreach ($permissions as $permission)
		{
			if (in_array($permission, $this->getPermissions()))
				return true;
		}

		return false;
	}

	/**
	 * Add a permission to the role.
	 *
	 * @return boolean
	 */
	public function addPermission($permission)
	{
		if (!$this->hasDirectPermission($permission))
		{
			$permissionRecord = null;

			if (is_integer($permission))
				$permissionRecord = Permission::find($permission);

			if (is_string($permission))
				$permissionRecord = Permission::where('permission', $permission)->first();

			if (!empty($permissionRecord))
			{
				$this->rolePermissions()->attach($permissionRecord->id);

				return true;
			}
		}

		return false;
	}

	/**
	 * Remove a permission from the role.
	 *
	 * @return boolean
	 */
	public function removePermission($permission)
	{
		if ($this->hasDirectPermission($permission))
		{
			$permissionRecord = null;

			if (is_integer($permission))
				$permissionRecord = Permission::find($permission);

			if (is_string($permission))
				$permissionRecord = Permission::where('permission', $permission)->first();

			if (!empty($permissionRecord))
			{
				$this->rolePermissions()->detach($permissionRecord->id);

				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a permission has been directly applied to the role.
	 *
	 * @param  mixed    $permission
	 * @return boolean
	 */
	public function hasDirectPermission($permission)
	{
		foreach ($this->rolePermissions as $permissionListed)
		{
			if (is_integer($permission) && $permissionListed->id == $permission)
				return true;

			if (is_string($permission) && $permissionListed->permission == $permission)
				return true;
		}

		return false;
	}

}