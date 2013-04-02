<?php namespace Regulus\Identify;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;

class Role extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'roles';

	/**
	 * Turn off timestamps.
	 *
	 * @var string
	 */
	public $timestamps = false;

	/**
	 * The attributes that cannot be updated.
	 *
	 * @var array
	 */
	protected $guarded = array('id');

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
	 * @var array
	 */
	public function users()
	{
		return $this->hasMany('Regulus\Identify\User', 'role_id');
	}

}