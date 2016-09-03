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
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'email', 'password'];

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
	 * The permission sources array for the user.
	 *
	 * @var    array
	 */
	public $permissionSources = [];

	/**
	 * The route access statuses for the user.
	 *
	 * @var    array
	 */
	public $routeAccessStatuses = [];

	/**
	 * The access level of the user.
	 *
	 * @var    array
	 */
	public $accessLevel = null;

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
	public function __construct(array $attributes = [])
	{
		if (count($this->fillable) <= 3) // allow extended User model to override "fillable" directly rather than via config
			$this->fillable = config('auth.fillable_fields');

		parent::__construct($attributes);

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
			->orderBy('name')
			->withTimestamps();
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
			->orderBy('name')
			->withTimestamps();
	}

	/**
	 * The state that belongs to the user.
	 *
	 * @return StateItem
	 */
	public function stateItem()
	{
		return $this->hasOne('Regulus\Identify\Models\StateItem');
	}

	/**
	 * The state that belongs to the user.
	 *
	 * @return CachedPermissionsRecord
	 */
	public function cachedPermissionsRecord()
	{
		return $this->hasOne('Regulus\Identify\Models\CachedPermissionsRecord');
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
	 * Filter out users that do not have a role or roles.
	 *
	 * @param  mixed    $roles
	 * @param  boolean  $joinRoles
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOnlyRoles($query, $roles, $joinRoles = true)
	{
		if (is_string($roles))
			$roles = [$roles];

		if ($joinRoles)
			$query = $this->scopeJoinRoles($query);

		return $query->whereIn('auth_roles.role', $roles);
	}

	/**
	 * Filter out users that have a role or roles.
	 *
	 * @param  mixed    $roles
	 * @param  boolean  $joinRoles
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeExceptRoles($query, $roles, $joinRoles = true)
	{
		if (is_string($roles))
			$roles = [$roles];

		if ($joinRoles)
			$query = $this->scopeJoinRoles($query);

		return $query->whereNotIn('auth_roles.role', $roles);
	}

	/**
	 * Join the roles for use with other scopes.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeJoinRoles($query)
	{
		return $query
			->select('auth_users.*')
			->leftJoin('auth_user_roles', 'auth_user_roles.user_id', '=', 'auth_users.id')
			->join('auth_roles', 'auth_roles.id', '=', 'auth_user_roles.role_id')
			->groupBy('auth_users.id');
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
		$name = str_replace('{user}', $this->{config('auth.username.field')}, $name);

		return trim($name);
	}

	/**
	 * Get the account activation URL for the user.
	 *
	 * @return string
	 */
	public function getActivationUrl()
	{
		return url('activate/'.$this->id.'/'.$this->activation_token);
	}

	/**
	 * Get the password reset email for a token.
	 *
	 * @param  mixed    $token
	 * @return string
	 */
	public function getPasswordResetUrl($token = null)
	{
		if (is_null($token))
		{
			$resetRequest = DB::table('password_resets')->where('email', $this->email)->orderBy('created_at', 'desc')->first();

			if ($resetRequest)
				$token = $resetRequest->token;
		}

		return url('password/reset', $token).'?email='.urlencode($this->email);
	}

	/**
	 * Reset the user's auth token.
	 *
	 * @return string
	 */
	public function resetAuthToken()
	{
		$authToken = str_random(128);

		$this->fill(['auth_token' => $authToken])->save();

		return $authToken;
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

		// format name, username, and email address
		if (isset($input['first_name']))
			$input['first_name'] = ucfirst(trim($input['first_name']));

		if (isset($input['last_name']))
			$input['last_name'] = ucfirst(trim($input['last_name']));

		if (isset($input['name']))
			$input['name'] = ucfirst(trim($input['name']));

		if (isset($input['email']))
			$input['email'] = trim($input['email']);

		if (!isset($input['first_name']) && !isset($input['last_name']))
			$input['first_name'] = $input['name'];

		$input['password'] = \Hash::make($input['password']);

		// set auth token
		$input['auth_token'] = str_random(128);

		// set activated timestamp or activation token
		if ($autoActivate)
			$input['activated_at'] = date('Y-m-d H:i:s');
		else
			$input['activation_token'] = str_random(32);

		// create user
		$user = static::create($input);

		// add user role(s)
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
			{
				$role = Role::where('default', true)->orderBy('id')->first();

				if (!empty($role))
					$roleId = $role->id;
			}

			if (!is_null($roleId))
				$roles = [$roleId];
		}

		$user->roles()->sync($roles);

		// add user permission(s)
		$permissions = [];
		if (isset($input['permissions']) && is_array($input['permissions']))
		{
			$permissions = $input['permissions'];
		}

		$user->userPermissions()->sync($permissions);

		// send account activation email to user
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
		if (!is_null($this->accessLevel))
			return $this->accessLevel;

		$this->accessLevel = 0;

		if ($this->access_level > $this->accessLevel)
			$this->accessLevel = $this->access_level;

		foreach ($this->roles as $role)
		{
			if ($role->access_level > $this->accessLevel)
				$this->accessLevel = $role->access_level;
		}

		return $this->accessLevel;
	}

	/**
	 * Get the permissions of the user.
	 *
	 * @param  boolean  $ignoreCached
	 * @param  string   $field
	 * @param  boolean  $returnSources
	 * @return array
	 */
	public function getPermissions($ignoreCached = false, $field = 'permission', $returnSources = false)
	{
		if (empty($this->permissions) || ($returnSources && empty($this->permissionSources)))
		{
			if (!$ignoreCached && $this->cachedPermissionsRecord)
			{
				if (!is_null($this->cachedPermissionsRecord->permissions))
					$this->permissions = json_decode($this->cachedPermissionsRecord->permissions);
			}
			else
			{
				// get user derived permissions
				foreach ($this->userPermissions as $permission)
				{
					if (!in_array($permission->{$field}, $this->permissions))
					{
						$this->permissions[] = $permission->{$field};
					}

					$this->permissionSources[$permission->permission] = "User";

					$this->addSubPermissionsToArray($permission, $field);
				}

				// get role derived permissions
				foreach ($this->roles as $role)
				{
					foreach ($role->rolePermissions as $permission)
					{
						if (!in_array($permission->{$field}, $this->permissions))
						{
							$this->permissions[] = $permission->{$field};
						}

						if (!isset($this->permissionSources[$permission->permission]))
						{
							$this->permissionSources[$permission->permission] = "Role:".$role->role.":".$role->name;
						}

						$this->addSubPermissionsToArray($permission, $field);
					}
				}

				// get access level derived permissions
				if (config('auth.enable_access_level'))
				{
					$permissions = Permission::where('access_level', '<=', $this->getAccessLevel())->get();
					foreach ($permissions as $permission)
					{
						if (!in_array($permission->{$field}, $this->permissions))
						{
							$this->permissions[] = $permission->{$field};
						}

						if (!isset($this->permissionSources[$permission->permission]))
						{
							$this->permissionSources[$permission->permission] = "Access Level";
						}

						$this->addSubPermissionsToArray($permission, $field);
					}
				}

				asort($this->permissions);
			}
		}

		if ($returnSources)
			return $this->permissionSources;

		return $this->permissions;
	}

	/**
	 * Get the permission names of the user.
	 *
	 * @return array
	 */
	public function getPermissionNames()
	{
		return $this->getPermissions(true, 'name');
	}

	/**
	 * Get the permission sources of the user.
	 *
	 * @return array
	 */
	public function getPermissionSources()
	{
		return $this->getPermissions(empty($this->permissionSources), 'permission', true);
	}

	/**
	 * Add the sub permissions of a permission to the permissions array.
	 *
	 * @param  object   $permission
	 * @param  string   $field
	 * @return void
	 */
	private function addSubPermissionsToArray($permission, $field)
	{
		foreach ($permission->subPermissions as $subPermission)
		{
			if (!in_array($subPermission->{$field}, $this->permissions))
			{
				$this->permissions[] = $subPermission->{$field};

				$this->addSubPermissionsToArray($subPermission, $field);
			}

			if (!isset($this->permissionSources[$subPermission->permission]))
				$this->permissionSources[$subPermission->permission] = "Permission:".$permission->permission.":".$permission->name;
		}
	}

	/**
	 * Check if a user has a particular permission.
	 *
	 * @param  mixed    $permissions
	 * @return boolean
	 */
	public function hasPermission($permissions)
	{
		$permissions = Auth::formatPermissionsArray($permissions);

		if (empty($permissions))
			return true;

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
		$permissions = Auth::formatPermissionsArray($permissions);

		foreach ($permissions as $permission)
		{
			if (!in_array($permission, $this->getPermissions()))
				return false;
		}

		return true;
	}

	/**
	 * An alias for hasPermission().
	 *
	 * @param  mixed    $permissions
	 * @return boolean
	 */
	public function can($permissions)
	{
		return $this->hasPermission($permissions);
	}

	/**
	 * Get the source of a permission for a user.
	 *
	 * @param  string   $permission
	 * @param  boolean  $includeRecordInfo
	 * @return mixed
	 */
	public function getPermissionSource($permission, $includeRecordInfo = false)
	{
		$permissionSources = $this->getPermissionSources();

		$type = null;
		$item = null;
		$name = null;

		if (array_key_exists($permission, $permissionSources))
		{
			$permissionSource = explode(':', $permissionSources[$permission]);

			$type = isset($permissionSource[0]) ? $permissionSource[0] : null;

			if (!$includeRecordInfo)
				return $type;

			$item = isset($permissionSource[1]) ? $permissionSource[1] : null;
			$name = isset($permissionSource[2]) ? $permissionSource[2] : null;
		}

		if ($includeRecordInfo)
			return (object) [
				'type' => $type,
				'item' => $item,
				'name' => $name,
			];

		return $type;
	}

	/**
	 * Cache permissions to reduce number of necessary permissions-related database queries per request.
	 *
	 * @return void
	 */
	public function cachePermissions()
	{
		$this->userPermissions = $this->userPermissions()->get();
		$this->roles           = $this->roles()->get();

		$permissions = $this->getPermissions(true);

		if (!empty($permissions))
			$permissions = json_encode(array_values($permissions));
		else
			$permissions = null;

		$data = ['permissions' => $permissions];

		if ($this->cachedPermissionsRecord)
			$this->cachedPermissionsRecord->fill($data)->save();
		else
			$this->cachedPermissionsRecord()->save(new CachedPermissionsRecord)->fill($data)->save();
	}

	/**
	 * Add a permission to the user.
	 *
	 * @param  mixed    $permission
	 * @param  boolean  $cache
	 * @return boolean
	 */
	public function addPermission($permission, $cache = true)
	{
		if (!$this->hasDirectPermission($permission))
		{
			$permissionRecord = null;

			if (is_integer($permission))
				$permissionRecord = Permission::find($permission);

			if (is_string($permission))
				$permissionRecord = Permission::where('permission', $permission)->first();

			if (!empty($permissionRecord))
			{
				$this->userPermissions()->attach($permissionRecord->id);

				if ($cache)
					$this->cachePermissions();

				return true;
			}
		}

		return false;
	}

	/**
	 * Remove a permission from the user.
	 *
	 * @param  mixed    $permission
	 * @param  boolean  $cache
	 * @return boolean
	 */
	public function removePermission($permission, $cache = true)
	{
		if ($this->hasDirectPermission($permission))
		{
			$permissionRecord = null;

			if (is_integer($permission))
				$permissionRecord = Permission::find($permission);

			if (is_string($permission))
				$permissionRecord = Permission::where('permission', $permission)->first();

			if (!empty($permissionRecord))
			{
				$this->userPermissions()->detach($permissionRecord->id);

				if ($cache)
					$this->cachePermissions();

				return true;
			}
		}

		return false;
	}

	/**
	 * Add multiple permissions to the user.
	 *
	 * @param  array    $permissions
	 * @return integer
	 */
	public function addPermissions(array $permissions)
	{
		$added = 0;

		foreach ($permissions as $permission)
		{
			$added += (int) $this->addPermission($permission, false);
		}

		$this->cachePermissions();

		return $added;
	}

	/**
	 * Remove multiple permissions to the user.
	 *
	 * @param  array    $permissions
	 * @return integer
	 */
	public function removePermissions(array $permissions)
	{
		$added = 0;

		foreach ($permissions as $permission)
		{
			$added += (int) $this->removePermission($permission, false);
		}

		$this->cachePermissions();

		return $added;
	}

	/**
	 * Check if a permission has been directly applied to the user.
	 *
	 * @param  mixed    $permission
	 * @return boolean
	 */
	public function hasDirectPermission($permission)
	{
		foreach ($this->userPermissions as $permissionListed)
		{
			if (is_integer($permission) && $permissionListed->id == $permission)
				return true;

			if (is_string($permission) && $permissionListed->permission == $permission)
				return true;
		}

		return false;
	}

	/**
	 * Get an array of roles for the user.
	 *
	 * @param  string   $field
	 * @return array
	 */
	public function getRoles($field = 'name')
	{
		if (!$this->roles)
			return [];

		return $this->roles()->lists($field)->toArray();
	}

	/**
	 * Checks whether the user is in one or all of the given roles ($roles can be an array of roles
	 * or a string of a single role).
	 *
	 * @param  mixed    $roles
	 * @param  boolean  $all
	 * @return boolean
	 */
	public function hasRole($roles, $all = false)
	{
		$allowed = false;
		$matches = 0;

		$userRoles = $this->roles;

		if (!is_array($roles))
			$roles = [$roles];

		foreach ($userRoles as $userRole)
		{
			foreach ($roles as $role)
			{
				if (strtolower($userRole->role) == strtolower($role))
				{
					$allowed = true;
					$matches ++;
				}
			}
		}

		if ($all && $matches < count($roles))
			$allowed = false;

		return $allowed;
	}

	/**
	 * Checks whether the user is in all of the given roles ($roles can be an array of roles
	 * or a string of a single role).
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public function hasRoles($roles)
	{
		return $this->hasRole($roles, true);
	}

	/**
	 * Alias of hasRole().
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public function isAll($roles)
	{
		return $this->hasRoles($roles);
	}

	/**
	 * A simple inversion of the is() method to check if a user should be denied access to the subsequent content.
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public function isNot($roles)
	{
		return !$this->hasRole($roles);
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
	 * Check if a user has access to a route.
	 *
	 * @param  mixed    $route
	 * @return boolean
	 */
	public function hasRouteAccess($route)
	{
		return Auth::hasRouteAccess($route, $this);
	}

	/**
	 * Check if current user has access to a route by URL.
	 *
	 * @param  string   $url
	 * @param  string   $verb
	 * @param  boolean  $default
	 * @return boolean
	 */
	public function hasAccess($url, $verb = 'get', $default = false)
	{
		return Auth::hasAccess($url, $verb, $default, $this);
	}

	/**
	 * Get route access statuses.
	 *
	 * @param  string   $routeName
	 * @param  boolean  $authorized
	 * @return array
	 */
	public function getRouteAccessStatuses()
	{
		return $this->routeAccessStatuses;
	}

	/**
	 * Set a route access status.
	 *
	 * @param  string   $routeName
	 * @param  boolean  $authorized
	 * @return void
	 */
	public function setRouteAccessStatus($routeName, $authorized)
	{
		$this->routeAccessStatuses[$routeName] = $authorized;
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
	public function checkState($name, $state = true, $default = null)
	{
		$value = $this->getState($name, $default);

		if (is_array($value) || is_object($value))
			return is_array($value) && in_array($state, $value);
		else
			return $value == $state;
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
		$fullName  = $name;
		$name      = explode('.', $name);

		if (count($name) == 1)
		{
			if (isset($stateData->{$name[0]}))
				return $stateData->{$name[0]};
		}
		else if (count($name) == 2)
		{
			if (isset($stateData->{$name[0]}) && is_object($stateData->{$name[0]}) && isset($stateData->{$name[0]}->{$name[1]}))
				return $stateData->{$name[0]}->{$name[1]};
		}
		else if (count($name) == 3)
		{
			if (isset($stateData->{$name[0]}) && is_object($stateData->{$name[0]})
			&& isset($stateData->{$name[0]}->{$name[1]}) && is_object($stateData->{$name[0]}->{$name[1]})
			&& isset($stateData->{$name[0]}->{$name[1]}->{$name[2]}))
				return $stateData->{$name[0]}->{$name[1]}->{$name[2]};
		}

		return !is_null($default) ? $default : config('auth.state_defaults.'.snake_case($fullName));
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
		}
		else
		{
			$name = explode('.', $name);

			if (count($name) == 1)
			{
				if (isset($stateData->{$name[0]}))
					unset($stateData->{$name[0]});
			}
			else if (count($name) == 2)
			{
				if (isset($stateData->{$name[0]}) && is_object($stateData->{$name[0]}) && isset($stateData->{$name[0]}->{$name[1]}))
				{
					unset($stateData->{$name[0]}->{$name[1]});

					if (empty($stateData->{$name[0]}))
						unset($stateData->{$name[0]});
				}
			}
			else if (count($name) == 3)
			{
				if (isset($stateData->{$name[0]}) && is_object($stateData->{$name[0]})
				&& isset($stateData->{$name[0]}->{$name[1]}) && is_object($stateData->{$name[0]}->{$name[1]})
				&& isset($stateData->{$name[0]}->{$name[1]}->{$name[2]}))
				{
					unset($stateData->{$name[0]}->{$name[1]}->{$name[2]});

					if (empty($stateData->{$name[0]}->{$name[1]}))
						unset($stateData->{$name[0]}->{$name[1]});

					if (empty($stateData->{$name[0]}))
						unset($stateData->{$name[0]});
				}
			}
		}

		if (!$this->stateItem)
		{
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

	/**
	 * Get a "unique" validation rule for a field such as username or email address that ignores soft-deleted records.
	 *
	 * @param  string   $field
	 * @param  mixed    $id
	 * @return string
	 */
	public static function getUniqueRule($field = 'email', $id = null)
	{
		$user = new static;

		return "unique:".$user->table.",".$field.($id ? ','.$id : ',NULL').",id,deleted_at,NULL";
	}

}