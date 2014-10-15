<?php namespace Regulus\Identify;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;

use Regulus\Identify\Identify as Auth;

use Regulus\Identify\Permission;

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
	 * Enable soft delete for the model.
	 *
	 * @var array
	 */
	use SoftDeletingTrait;

	protected $dates = ['deleted_at'];

	/**
	 * The permissions array for the user.
	 *
	 * @var    array
	 */
	public $permissions = array();

	/**
	 * The access level of the user.
	 *
	 * @var    array
	 */
	public $accessLevel = 0;

	/**
	 * The state data for the user.
	 *
	 * @var    array
	 */
	public $stateData = array();

	/**
	 * The constructor which adds the table prefix from the config settings.
	 *
	 */
	public function __construct()
	{
		$this->table = Config::get('identify::tablePrefix').$this->table;
	}

	/**
	 * The roles of the user.
	 *
	 * @return Collection
	 */
	public function roles()
	{
		return $this->belongsToMany('Regulus\Identify\Role', Config::get('identify::tablePrefix').'user_roles')
			->orderBy('display_order')
			->orderBy('name');
	}

	/**
	 * The permissions of the user.
	 *
	 * @return Collection
	 */
	public function userPermissions()
	{
		return $this->belongsToMany('Regulus\Identify\Permission', Config::get('identify::tablePrefix').'user_permissions')
			->orderBy('display_order')
			->orderBy('name');
	}

	/**
	 * The state that belongs to the user.
	 *
	 * @return object
	 */
	public function stateItem()
	{
		return $this->hasOne('Regulus\Identify\StateItem');
	}

	/**
	 * Allow user to be used in polymorphic relationships.
	 *
	 * @return Collection
	 */
	public function content()
	{
		return $this->morphTo();
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
	 * Check whether user's account is active (activated and not banned).
	 *
	 * @return boolean
	 */
	public function isActive()
	{
		return $this->isActivated() && !$this->isBanned();
	}

	/**
	 * Check whether user's account is activated.
	 *
	 * @return boolean
	 */
	public function isActivated()
	{
		return $this->activated_at != null;
	}

	/**
	 * Check whether user's account is banned.
	 *
	 * @return boolean
	 */
	public function isBanned()
	{
		return $this->banned_at != null;
	}

	/**
	 * Get the reason a user was banned.
	 *
	 * @return string
	 */
	public function getBanReason()
	{
		if (!$this->isBanned())
			return "";

		return $this->ban_reason;
	}

	/**
	 * Get the picture for the user.
	 *
	 * @param  boolean  $thumbnail
	 * @return string
	 */
	public function getPicture($thumbnail = false)
	{
		$picture = URL::asset('assets/images/display-pic-default.png');

		if ( ! $thumbnail) {
			$file = Config::get('identify::pathPicture').Config::get('identify::filenamePicture');
		} else {
			$file = Config::get('identify::pathPictureThumbnail').Config::get('identify::filenamePictureThumbnail');
		}
		$file = str_replace(':userID', $this->id, $file);

		if (is_file($file)) {
			$picture = URL::to($file);
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
		$name = $this->first_name;
		if ($this->last_name != "") {
			if ($name != "") $name .= " ";
			$name .= $this->last_name;
		}
		return $name;
	}

	/**
	 * Attempt to activate a user account by the user ID and activation code.
	 *
	 * @param  integer  $id
	 * @param  string   $activationCode
	 * @return boolean
	 */
	public static function activate($id = 0, $activationCode = '')
	{
		$user = User::find($id);
		if (!empty($user) && !$user->activated && (static::is('admin') || $activationCode == $user->activation_code)) {
			$user->activated_at = date('Y-m-d H:i:s');
			$user->save();
			return true;
		}
		return false;
	}

	/**
	 * Create a new user account.
	 *
	 * @return boolean
	 */
	public static function createAccount()
	{
		//check for role
		if (Auth::is('admin')) {
			$roleName = Input::get('role');
		} else {
			$roleName = "Member";
		}
		$role = Role::where('name', '=', $roleName)->first();
		if (empty($role)) return false;

		$user = new static;
		$user->updateAccount('create');

		//add user role
		$userRole = new UserRole;
		$userRole->user_id = $user->id;
		$userRole->role_id = $role->id;
		$userRole->save();

		//send account activation email to registrant
		Auth::sendEmail($user, 'signup_confirmation');

		return $user;
	}

	/**
	 * Update a user account.
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

	/**
	 * Create a reset password code for a user.
	 *
	 * @return boolean
	 */
	public function resetPasswordCode()
	{
		$this->updateAccount('passwordReset');
		Auth::sendEmail($this, 'reset_password');
		return true;
	}

	/**
	 * Get an active user by ID.
	 *
	 * @param  integer  $id
	 * @return object
	 */
	public static function getActiveById($id)
	{
		return static::orderBy('id')
			->where('id', $id)
			->where('activated_at', '!=', null)
			->where('banned_at', null)
			->where('deleted_at', null)
			->first();
	}

	/**
	 * Get a user by their username or email address.
	 *
	 * @param  string   $identifier
	 * @return boolean
	 */
	public static function getByUsernameOrEmail($identifier = '')
	{
		$identifier = trim(strtolower($identifier));
		return User::where(function($query) use ($identifier)
		{
			$query
				->where(DB::raw('lower(username)'), '=', $identifier)
				->orWhere(DB::raw('lower(email)'), '=', $identifier);
		})->first();
	}

	/**
	 * Get the remember token.
	 *
	 * @return string
	 */
	public function getRememberToken()
	{
		return $this->remember_token;
	}

	/**
	 * Get the remember token.
	 *
	 * @return void
	 */
	public function setRememberToken($value)
	{
		$this->remember_token = $value;
	}

	/**
	 * Get the name of the remember token field.
	 *
	 * @return string
	 */
	public function getRememberTokenName()
	{
		return 'remember_token';
	}

	/**
	 * Get the highest access level assigned to the user, either directly or via the user's roles.
	 *
	 * @return integer
	 */
	public function getAccessLevel()
	{
		$this->accessLevel = 0;

		if ($this->access_level > $accessLevel)
			$this->accessLevel = $this->access_level;

		foreach ($this->roles as $role) {
			if ($role->access_level > $accessLevel)
				$this->accessLevel = $role->access_level;
		}

		return $this->accessLevel;
	}

	/**
	 * Get the permissions of the user.
	 *
	 * @return array
	 */
	public function getPermissions()
	{
		if (empty($this->permissions))
		{
			//get user derived permissions
			foreach ($this->userPermissions as $permission) {
				if (!in_array($permission->name, $permissions))
					$this->permissions[] = $permission->name;
			}

			//get role derived permissions
			foreach ($this->roles as $role) {
				foreach ($role->rolePermissions as $permission) {
					if (!in_array($permission->name, $permissions))
						$this->permissions[] = $permission->name;
				}
			}

			//get access level derived permissions
			$permissions = Permission::where('access_level', '<=', $this->getAccessLevel)->get();
			foreach ($permissions as $permission) {
				if (!in_array($permission->name, $permissions))
					$this->permissions[] = $permission->name;
			}

			asort($this->permissions);
		}

		return $this->permissions;
	}

	/**
	 * Check if a user has a particular permission.
	 *
	 * @param  string   $permission
	 * @return boolean
	 */
	public function hasPermission($permission)
	{
		return in_array($permission, $this->getPermissions());
	}

	/**
	 * Check if a user has a particular access level.
	 *
	 * @param  integer  $level
	 * @return boolean
	 */
	public function hasAccessLevel($level)
	{
		return $this->getAccessLevel() >= (int) $level;
	}

	/**
	 * Get the state data for a user.
	 *
	 * @return object
	 */
	public function getStateData()
	{
		if (empty($this->stateData))
		{
			if ($this->stateItem && !is_null($this->stateItem->data))
				$this->stateData = json_decode($this->stateItem->data);
			else
				$this->stateData = (object) array();
		}

		return $this->stateData;
	}

	/**
	 * Check a particular state for a user.
	 *
	 * @param  string   $name
	 * @param  mixed    $state
	 * @param  mixed    $default
	 * @return boolean
	 */
	public function checkState($name, $state = true, $default = false)
	{
		$stateData = $this->getStateData();

		if (!isset($stateData->{$name}))
			return $default;

		if (is_array($stateData->{$name}))
			return is_array($stateData->{$name}) && in_array($state, $stateData->{$name});
		else
			return $stateData->{$name} == $state;
	}

	/**
	 * Get a particular state for a user.
	 *
	 * @param  string   $name
	 * @param  mixed    $default
	 * @return mixed
	 */
	public function getState($name, $default = null)
	{
		$stateData = $this->getStateData();

		if (!isset($stateData->{$name}))
			return $default;

		return $stateData->{$name};
	}

	/**
	 * Set a particular state for a user.
	 *
	 * @param  string   $name
	 * @param  mixed    $state
	 * @return boolean
	 */
	public function setState($name, $state = true)
	{
		if ($state == "true")
			$state = 1;

		if ($state == "false")
			$state = 0;

		if (is_bool($state))
			$state = (int) $state;

		$stateData = $this->getStateData();

		if (substr($name, -2) == "[]")
		{
			$name = str_replace('[]', '', $name);

			if (!isset($stateData->{$name}) || !is_array($stateData->{$name}))
				$stateData->{$name} = [];

			if (!in_array($state, $stateData->{$name}))
				$stateData->{$name}[] = $state;

		} else {
			$stateData->{$name} = $state;
		}

		if (!$this->stateItem) {
			$this->stateItem = new StateItem;
			$this->stateItem->user_id = $this->id;
		}

		$this->stateItem->data = json_encode($stateData);
		$this->stateItem->save();

		return true;
	}

	/**
	 * Remove a particular state for a user.
	 *
	 * @param  string   $name
	 * @param  mixed    $state
	 * @return boolean
	 */
	public function removeState($name, $state = true)
	{
		if ($state == "true")
			$state = 1;

		if ($state == "false")
			$state = 0;

		if (is_bool($state))
			$state = (int) $state;

		$stateData = $this->getStateData();

		if (substr($name, -2) == "[]")
		{
			$name = str_replace('[]', '', $name);

			if (!isset($stateData->{$name}) || !is_array($stateData->{$name}))
				$stateData->{$name} = [];

			if (in_array($state, $stateData->{$name}))
			{
				foreach ($stateData->{$name} as $key => $value) {
					if ($value == $state)
						unset($stateData->{$name}[$key]);
				}

				if (empty($stateData->{$name}))
					unset($stateData->{$name});
			}
		} else {
			if (isset($stateData->{$name}))
				unset($stateData->{$name});
		}

		if (!$this->stateItem) {
			$this->stateItem = new StateItem;
			$this->stateItem->user_id = $this->id;
		}

		$this->stateItem->data = json_encode($stateData);

		if ($this->stateItem->data == "{}")
			$this->stateItem->data = null;

		$this->stateItem->save();

		return true;
	}

	/**
	 * Clear state data for a user.
	 *
	 * @return boolean
	 */
	public function clearStateData($state)
	{
		if (!$this->stateItem)
			return false;

		$this->stateItem->data = null;
		$this->stateItem->save();
		return true;
	}

}