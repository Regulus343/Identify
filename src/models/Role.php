<?php namespace Regulus\Identify;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use Illuminate\Support\Facades\Config;

use Regulus\Identify\Permission;

class Role extends Eloquent {

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
	protected $guarded = array('id');

	/**
	 * Enable soft delete for the model.
	 *
	 * @var array
	 */
	use SoftDeletingTrait;

	protected $dates = ['deleted_at'];

	/**
	 * The permissions array for the role.
	 *
	 * @var    array
	 */
	public $permissions = array();

	/**
	 * The constructor which adds the table prefix from the config settings.
	 *
	 */
	public function __construct()
	{
		$this->table = Config::get('identify::tablePrefix').$this->table;
	}

	/**
	 * The users of the role.
	 *
	 * @return Collection
	 */
	public function users()
	{
		return $this->belongsToMany('Regulus\Identify\User', Config::get('identify::tablePrefix').'user_roles')
			->orderBy('username');
	}

	/**
	 * The permissions of the role.
	 *
	 * @return Collection
	 */
	public function rolePermissions()
	{
		return $this->belongsToMany('Regulus\Identify\Permission', Config::get('identify::tablePrefix').'role_permissions')
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
			$select = array('role', 'name');

		if (count($select) == 1)
			$select[1] = $select[0];

		$roles   = static::orderBy('display_order')->get();
		$options = array();
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
		if (empty($this->permissions)) {
			$this->permissions = array();

			//get role derived permissions
			foreach ($this->roles as $role) {
				foreach ($role->rolePermissions as $permission) {
					if (!in_array($permission->{$field}, $permissions))
						$this->permissions[] = $permission->{$field};
				}
			}

			//get access level derived permissions
			$permissions = Permission::where('access_level', '<=', $this->getAccessLevel)->get();
			foreach ($permissions as $permission) {
				if (!in_array($permission->{$field}, $permissions))
					$this->permissions[] = $permission->{$field};
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

}