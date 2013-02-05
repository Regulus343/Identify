<?php

use Illuminate\Auth\UserInterface;

class User extends Eloquent implements UserInterface {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password');

	/**
	 * The role of the user.
	 *
	 * @var array
	 */
	public function roles()
	{
		return $this->belongsToMany('Role', 'user_roles');
	}

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * Get the picture for the user.
	 *
	 * @return string
	 */
	public function getPicture()
	{
		$picture = URL::asset('assets/img/display-pic-default.png');
		if (is_file('uploads/user_images/thumbs/'.$this->id.'.jpg')) {
			$picture = URL::to('uploads/user_images/thumbs/'.$this->id.'.jpg');
		}
		return $picture;
	}

	/**
	 * Get the name of the user.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->first_name.' '.$this->last_name;
	}

	/**
	 * Update user account.
	 *
	 * @return boolean
	 */
	public function updateAccount()
	{
		$this->username   = trim(Input::get('username'));
		$this->email      = trim(Input::get('email'));
		$this->first_name = ucfirst(trim(Input::get('first_name')));
		$this->last_name  = ucfirst(trim(Input::get('first_name')));
		$this->website    = trim(Input::get('website'));
		$this->twitter    =	trim(Input::get('twitter'));

		$purifier         = new HTMLPurifier;
		$this->about      = $purifier->purify(Input::get('about'));

		$this->listed = 0;
		$this->listed_email = 0;

		if (isset($_POST['listed']))		$this->listed = 1;
		if (isset($_POST['listed_email']))	$this->listed_email = 1;

		$this->save();
		return true;
	}

}