<?php

class Role extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'roles';

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