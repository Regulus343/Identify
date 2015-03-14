<?php namespace Regulus\Identify\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;

use Auth;

use Regulus\Identify\Permission;

use Regulus\TetraText\Facade as Format;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes;

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
	protected $hidden = ['password', 'remember_token'];

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
	 * The permissions array for the user.
	 *
	 * @var    array
	 */
	public $permissions = [];

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
	public $stateData = [];

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
	 * The roles of the user.
	 *
	 * @return Collection
	 */
	public function roles()
	{
		return $this->belongsToMany('Regulus\Identify\Models\Role', Auth::getTableName('user_roles'))
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
		return $this->belongsToMany('Regulus\Identify\Models\Permission', Auth::getTableName('user_permissions'))
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
		return $this->hasOne('Regulus\Identify\Models\StateItem');
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
		return !is_null($this->activated_at);
	}

	/**
	 * Check whether user's account is banned.
	 *
	 * @return boolean
	 */
	public function isBanned()
	{
		return !is_null($this->banned_at);
	}

	/**
	 * Get the reason a user was banned.
	 *
	 * @return string
	 */
	public function getBanReason()
	{
		if (!$this->isBanned())
			return null;

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

		if (!$thumbnail)
			$file = config('auth.path_picture').config('auth.filename_picture');
		else
			$file = config('auth.path_picture_thumbnail').config('auth.filename_picture_thumbnail');

		$file = str_replace(':userId', $this->id, $file);

		if (is_file($file))
			$picture = URL::to($file);

		return $picture;
	}

	/**
	 * Get the name of the user.
	 *
	 * @return string
	 */
	public function getName($format = 'F L')
	{
		$name = str_replace('F', '{first}', $format);
		$name = str_replace('L', '{last}', $name);
		$name = str_replace('U', '{user}', $name);

		$name = str_replace('{first}', $this->first_name, $name);
		$name = str_replace('{last}', $this->last_name, $name);
		$name = str_replace('{user}', $this->name, $name);

		return trim($name);
	}

	/**
	 * Attempt to activate a user account by the user ID and activation token.
	 *
	 * @param  integer  $id
	 * @param  string   $activationToken
	 * @return boolean
	 */
	public static function activate($id, $activationToken = '')
	{
		$user = User::find($id);

		if (!empty($user) && !$user->activated && (static::is('admin') || $activationToken == $user->activation_token))
		{
			$user->activated_at = date('Y-m-d H:i:s');
			$user->save();

			return true;
		}

		return false;
	}

	/**
	 * Create a new user account.
	 *
	 * @param  mixed    $input
	 * @param  boolean  $autoActivate
	 * @param  boolean  $sendEmail
	 * @return User
	 */
	public static function createAccount($input = null, $autoActivate = false, $sendEmail = true)
	{
		//get input values
		if (is_null($input))
			$input = Input::except('id');

		//format name, username, and email address
		if (isset($input['first_name']))
			$input['first_name'] = ucfirst(trim($input['first_name']));

		if (isset($input['last_name']))
			$input['last_name'] = ucfirst(trim($input['last_name']));

		if (isset($input['name']))
			$input['name'] = trim($input['name']);

		if (isset($input['email']))
			$input['email'] = trim($input['email']);

		if (!isset($input['first_name']) && !isset($input['last_name']))
			$input['first_name'] = $input['name'];

		//set activated timestamp or activation token
		if ($autoActivate)
			$input['activated_at'] = date('Y-m-d H:i:s');
		else
			$input['activation_token'] = str_random(32);

		$input['password'] = \Hash::make($input['password']);

		//create user
		$user = new static;
		$user->fill($input)->save();

		//add user role(s)
		$roles = [];
		if (isset($input['roles']) && is_array($input['roles']))
		{
			$roles = $input['roles'];
		}
		else
		{
			$roleId = Auth::is('admin') && isset($input['role_id']) ? $input['role_id'] : null;
			$role   = Role::find($roleId);

			if (empty($role) || is_null($roleId))
				$role = Role::where('default', true)->orderBy('id')->first();

			$roles = [$roleId];
		}

		$user->roles()->sync($roles);

		//send account activation email to user
		if ($sendEmail)
			Auth::sendEmail($user, 'confirmation');

		return $user;
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
			->whereNotNull('activated_at')
			->whereNull('banned_at')
			->whereNull('deleted_at')
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
					->where(DB::raw('lower(name)'), $identifier)
					->orWhere(DB::raw('lower(email)'), $identifier);
			})->first();
	}

	/**
	 * Get the username, which could be called "name" or "username" and is specified in the auth config file.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function username()
	{
		return $this->{config('auth.username.field')};
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

		foreach ($this->roles as $role)
		{
			if ($role->access_level > $accessLevel)
				$this->accessLevel = $role->access_level;
		}

		return $this->accessLevel;
	}

	/**
	 * Get the permissions of the user.
	 *
	 * @param  string   $field
	 * @return array
	 */
	public function getPermissions($field = 'permission')
	{
		if (empty($this->permissions))
		{
			//get user derived permissions
			foreach ($this->userPermissions as $permission)
			{
				if (!in_array($permission->{$field}, $permissions))
					$this->permissions[] = $permission->{$field};
			}

			//get role derived permissions
			foreach ($this->roles as $role) {
				foreach ($role->rolePermissions as $permission)
				{
					if (!in_array($permission->{$field}, $permissions))
						$this->permissions[] = $permission->{$field};
				}
			}

			//get access level derived permissions
			$permissions = Permission::where('access_level', '<=', $this->getAccessLevel)->get();
			foreach ($permissions as $permission)
			{
				if (!in_array($permission->{$field}, $permissions))
					$this->permissions[] = $permission->{$field};
			}

			asort($this->permissions);
		}

		return $this->permissions;
	}

	/**
	 * Get the permission names of the user.
	 *
	 * @return array
	 */
	public function getPermissionNames()
	{
		return $this->getPermissions('name');
	}

	/**
	 * Check if a user has a particular permission.
	 *
	 * @param  mixed    $permissions
	 * @return boolean
	 */
	public function hasPermission($permissions)
	{
		if (is_string($permissions))
			$permissions = [$permissions];

		foreach ($permissions as $permission)
		{
			if (in_array($permission, $this->getPermissions()))
				return true;
		}

		return false;
	}

	/**
	 * Check if a user has a set of specified permissions.
	 *
	 * @param  mixed    $permissions
	 * @return boolean
	 */
	public function hasPermissions($permissions)
	{
		if (is_string($permissions))
			$permissions = [$permissions];

		foreach ($permissions as $permission)
		{
			if (!in_array($permission, $this->getPermissions()))
				return false;
		}

		return true;
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
				$this->stateData = (object) [];
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

		if (is_array($stateData->{$name}) || is_object($stateData->{$name}))
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

		if (is_null($stateData))
			$stateData = (object) [];

		if (substr($name, -2) == "[]")
		{
			$name = str_replace('[]', '', $name);

			if (!isset($stateData->{$name}) || !is_array($stateData->{$name}))
				$stateData->{$name} = [];

			if (!in_array($state, $stateData->{$name}))
				$stateData->{$name}[] = $state;

		} else {
			$name = explode('.', $name);

			if (count($name) == 1)
			{
				$stateData->{$name[0]} = $state;
			}
			else
			{
				if (!isset($stateData->{$name[0]}) || !is_object($stateData->{$name[0]}))
					$stateData->{$name[0]} = (object) [];

				if (count($name) == 2)
				{
					$stateData->{$name[0]}->{$name[1]} = $state;
				}
				else
				{
					if (!isset($stateData->{$name[0]}->{$name[1]}) || !is_object($stateData->{$name[0]}->{$name[1]}))
						$stateData->{$name[0]}->{$name[1]} = (object) [];

					$stateData->{$name[0]}->{$name[1]}->{$name[2]} = $state;
				}
			}
		}

		if (!$this->stateItem)
		{
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
				foreach ($stateData->{$name} as $key => $value)
				{
					if ($value == $state)
						unset($stateData->{$name}[$key]);
				}

				if (empty($stateData->{$name}))
					unset($stateData->{$name});
			}
		} else {
			$name = explode('.', $name);

			if (count($name) == 1)
			{
				if (isset($stateData->{$name[0]}))
					unset($stateData->{$name[0]});
			}
			else if (count($name) == 2)
			{
				if (isset($stateData->{$name[0]}) && isset($stateData->{$name[0]}->{$name[1]}))
					unset($stateData->{$name[0]}->{$name[1]});
			}
			else if (count($name) == 3)
			{
				if (isset($stateData->{$name[0]}) && isset($stateData->{$name[0]}->{$name[1]} && isset($stateData->{$name[0]}->{$name[1]}->{$name[2]}))
					unset($stateData->{$name[0]}->{$name[1]}->{$name[2]});
			}

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