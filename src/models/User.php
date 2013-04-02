<?php namespace Regulus\Identify;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;

class User extends Eloquent implements UserInterface, RemindableInterface {

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
	 * The role of the user.
	 *
	 * @var array
	 */
	public function roles()
	{
		return $this->belongsToMany('Regulus\Identify\Role', Config::get('identify::tablePrefix').'user_roles');
	}

	/**
	 * Allow user to be used in polymorphic relationships.
	 *
	 * @var array
	 */
	public function events()
	{
		return $this->belongsTo('\SocialEvent', 'user_id');
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
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
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
	public function updateAccount($types = 'standard')
	{
		$dataSetup = Config::get('identify::dataSetup');
		if (is_string($types)) $types = array($types);
		foreach ($types as $type) {
			if (isset($dataSetup[$type])) {
				foreach ($dataSetup[$type] as $field => $value) {
					$this->{$field} = $value;
				}
			}
		}

		$this->save();
		return true;
	}

}