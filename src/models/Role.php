<?php namespace Regulus\Identify;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;

class Role extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = Config::get('identify::tablePrefix').'roles';

	/**
	 * Turn off timestamps.
	 *
	 * @var string
	 */
	public $timestamps = false;

	/**
	 * Belongs to User.
	 *
	 * @var array
	 */
	public function users()
	{
		return $this->hasMany('User', 'role_id');
	}

}