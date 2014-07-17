<?php namespace Regulus\Identify;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use Illuminate\Support\Facades\Config;

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
	 * The constructor which adds the table prefix from the config settings.
	 *
	 */
	public function __construct()
	{
		$this->table = Config::get('identify::tablePrefix').$this->table;
	}

	/**
	 * Belongs to User.
	 *
	 * @return Collection
	 */
	public function users()
	{
		return $this->belongsToMany('Regulus\Identify\User', Config::get('identify::tablePrefix').'user_roles')
			->orderBy('username');
	}

	/**
	 * Belongs to User.
	 *
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

}