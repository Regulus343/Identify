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
	 * The permissions array for the role.
	 *
	 * @var    array
	 */
	public $permissions = [];

	/**
	 * The constructor which adds the table prefix from the config settings.
	 *
	 */
	public function __construct()
	{
		parent::__construct();

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
		if (empty($this->permissions)) {
			$this->permissions = [];

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