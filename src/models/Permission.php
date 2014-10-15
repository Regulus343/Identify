<?php namespace Regulus\Identify;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use Illuminate\Support\Facades\Config;

class Permission extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'permissions';

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
	 * The constructor which adds the table prefix from the config settings.
	 *
	 */
	public function __construct()
	{
		$this->table = Config::get('identify::tablePrefix').$this->table;
	}

	/**
	 * The users that have the permission.
	 *
	 * @return Collection
	 */
	public function users()
	{
		return $this->belongsToMany('Regulus\Identify\User', Config::get('identify::tablePrefix').'user_permissions')
			->orderBy('username');
	}

	/**
	 * The roles that have the permission.
	 *
	 * @return Collection
	 */
	public function roles()
	{
		return $this->belongsToMany('Regulus\Identify\Role', Config::get('identify::tablePrefix').'role_permissions')
			->orderBy('name');
	}

	/**
	 * Get a select box list of permissions.
	 *
	 * @param  mixed    $select
	 * @return array
	 */
	public static function getSelectable($select = null)
	{
		if (is_null($select) || !is_array($select) || count($select) == 0)
			$select = array('id', 'name');

		if (count($select) == 1)
			$select[1] = $select[0];

		$permissions   = static::orderBy('name')->get();
		$options = array();
		foreach ($permissions as $permission) {
			$options[$permission->{$select[0]}] = $permission->{$select[1]};
		}

		return $options;
	}

}