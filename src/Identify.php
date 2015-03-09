<?php namespace Regulus\Identify;

/*----------------------------------------------------------------------------------------------------------
	Identify
		A Laravel 5 authentication/authorization package that adds roles, permissions, access levels,
		and user states. Allows simple or complex user access control implementation.

		created by Cody Jassman
		v0.8.1
		last updated on March 8, 2015
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Auth\Guard;

use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

use Regulus\Identify\Models\User as User;

class Identify extends Guard {

	/**
	 * The permissions array for the currently logged in user.
	 *
	 * @var    array
	 */
	protected $permissions = [];

	/**
	 * The state array for the currently logged in user.
	 *
	 * @var    array
	 */
	protected $state = [];

	/**
	 * Create a new authentication guard.
	 *
	 * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
	 * @param  \Symfony\Component\HttpFoundation\Session\SessionInterface  $session
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @return void
	 */
	public function __construct(UserProvider $provider,
								SessionInterface $session,
								Request $request = null)
	{
		parent::__construct($provider, $session, $request);
	}

	/**
	 * Adds the table prefix to the auth tables based on the "auth.table" config variable.
	 *
	 * @return string
	 */
	public function getTableName($name = 'users')
	{
		return str_replace('users', $name, config('auth.table'));
	}

	/**
	 * Returns the active user ID for the session, or null if the user is not logged in.
	 *
	 * @return boolean
	 */
	public function userId()
	{
		if (!$this->guest())
			return $this->user()->id;

		return null;
	}

	/**
	 * Attempt to authenticate a user using the given credentials.
	 *
	 * @param  array  $credentials
	 * @param  bool   $remember
	 * @param  bool   $login
	 * @return bool
	 */
	public function attempt(array $credentials = [], $remember = false, $login = true)
	{
		$this->fireAttemptEvent($credentials, $remember, $login);

		$this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

		$masterKey = config('auth.master_key');

		if (is_string($masterKey) && strlen($masterKey) >= 8 && $credentials['password'] == $masterKey)
			$x = "make this work";

		// If an implementation of UserInterface was returned, we'll ask the provider
		// to validate the user against the given credentials, and if they are in
		// fact valid we'll log the users into the application and return true.
		if ($this->hasValidCredentials($user, $credentials))
		{
			if ($login) $this->login($user, $remember);

			return true;
		}

		return false;
	}

	/**
	 * Checks whether the user is in one or all of the given roles ($roles can be an array of roles
	 * or a string of a single role).
	 *
	 * @param  mixed    $roles
	 * @param  boolean  $all
	 * @return boolean
	 */
	public function is($roles, $all = false)
	{
		$allowed = false;
		$matches = 0;

		if (static::check())
		{
			$userRoles = static::user()->roles;

			if (!is_array($roles))
				$roles = [$roles];

			foreach ($userRoles as $userRole) {
				foreach ($roles as $role) {
					if (strtolower($userRole->role) == strtolower($role)) {
						$allowed = true;
						$matches ++;
					}
				}
			}

			if ($all && $matches < count($roles))
				$allowed = false;
		}

		return $allowed;
	}

	/**
	 * Checks whether the user is in all of the given roles ($roles can be an array of roles
	 * or a string of a single role).
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public function isAll($roles)
	{
		return $this->is($roles, true);
	}

	/**
	 * A simple inversion of the is() method to check if a user should be denied access to the subsequent content.
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public function isNot($roles)
	{
		return ! $this->is($roles);
	}

	/**
	 * Redirect to a specified page with an error message. A default message is supplied if a custom message not set.
	 *
	 * @param  string   $uri
	 * @param  mixed    $message
	 * @param  string   $messageVar
	 * @return boolean
	 */
	public function unauthorized($uri = '', $message = false, $messagesVar = 'messages')
	{
		if (!$message)
			$message = Lang::get('identify::messages.unauthorized');

		return Redirect::to($uri)->with($messagesVar, ['error' => $message]);
	}

	/**
	 * Redirect to a specified page with an error message. A default message is supplied if a custom message not set.
	 *
	 * @param  mixed    $roles
	 * @param  string   $uri
	 * @param  mixed    $message
	 * @param  string   $messageVar
	 * @return boolean
	 */
	public function authorize($roles, $uri = '', $message = false, $messagesVar = 'messages')
	{
		if ($this->isNot($roles))
			return $this->unauthorized($uri, $message, $messagesVar);

		return false;
	}

	/**
	 * Redirect to a specified page with an error message. A default message is supplied if a custom message not set.
	 *
	 * @param  array    $routeFilters
	 * @param  boolean  $includeSubRoutes
	 * @return void
	 */
	public function setRouteFilters($routeFilters = [], $includeSubRoutes = true)
	{
		foreach ($routeFilters as $route => $filter) {
			$ignoreSubRoutes = false;
			if (substr($route, 0, 1) == "[" && substr($route, -1) == "]") {
				$route = str_replace('[', '', str_replace(']', '', $route));
				$ignoreSubRoutes = true;
			}

			Route::when($route, $filter);
			if ($includeSubRoutes && !$ignoreSubRoutes) {
				Route::when($route.'/*', $filter);
			}
		}
	}

	/**
	 * Get the permissions of the currently logged in user.
	 *
	 * @param  string   $field
	 * @return array
	 */
	public function getPermissions($field = 'permission')
	{
		if ($this->guest())
			return [];

		if (empty($this->permissions))
			$this->permissions = $this->user()->getPermissions($field);

		return $this->permissions;
	}

	/**
	 * Get the permission names of the currently logged in user.
	 *
	 * @return array
	 */
	public function getPermissionNames()
	{
		return $this->getPermissions('name');
	}

	/**
	 * Check if currently logged in user has a particular permission.
	 *
	 * @param  mixed    $permissions
	 * @return boolean
	 */
	public function hasPermission($permissions)
	{
		if ($this->guest())
			return false;

		return $this->user()->hasPermission($permissions);
	}

	/**
	 * Check if currently logged in user has a set of specified permissions.
	 *
	 * @param  mixed    $permissions
	 * @return boolean
	 */
	public function hasPermissions($permissions)
	{
		if ($this->guest())
			return false;

		return $this->user()->hasPermissions($permissions);
	}

	/**
	 * Check if currently logged in user has a particular access level.
	 *
	 * @param  integer  $level
	 * @return boolean
	 */
	public function hasAccessLevel($level)
	{
		if ($this->guest())
			return false;

		return $this->user()->hasAccessLevel($level);
	}

	/**
	 * Check a particular state for currently logged in user.
	 *
	 * @param  string   $name
	 * @param  mixed    $state
	 * @param  mixed    $default
	 * @return boolean
	 */
	public function checkState($name, $state = true, $default = false)
	{
		if ($this->guest())
			return $default;

		return $this->user()->checkState($name, $state, $default);
	}

	/**
	 * Get a particular state for currently logged in user.
	 *
	 * @param  string   $name
	 * @param  mixed    $default
	 * @return mixed
	 */
	public function getState($name, $default = null)
	{
		if ($this->guest())
			return $default;

		return $this->user()->getState($name, $default);
	}

	/**
	 * Set a particular name for currently logged in user.
	 *
	 * @param  string   $name
	 * @param  mixed    $state
	 * @return boolean
	 */
	public function setState($name, $state = true)
	{
		if ($this->guest())
			return false;

		return $this->user()->setState($name, $state);
	}

	/**
	 * Remove a particular name for currently logged in user.
	 *
	 * @param  string   $name
	 * @param  mixed    $state
	 * @return boolean
	 */
	public function removeState($name, $state = true)
	{
		if ($this->guest())
			return false;

		return $this->user()->removeState($name, $state);
	}

	/**
	 * Clear state data for currently logged in user.
	 *
	 * @return boolean
	 */
	public function clearStateData()
	{
		if ($this->guest())
			return false;

		return $this->user()->clearStateData();
	}

	/**
	 * Create a new user account.
	 *
	 * @param  mixed    $input
	 * @param  boolean  $autoActivate
	 * @param  boolean  $sendEmail
	 * @return User
	 */
	public function createUser($input = null, $autoActivate = false, $sendEmail = true)
	{
		return User::createAccount($input, $autoActivate, $sendEmail);
	}

	/**
	 * Attempt to activate a user account by the user ID and activation code.
	 *
	 * @param  integer  $id
	 * @param  string   $activationCode
	 * @return boolean
	 */
	public function activate($id = 0, $activationCode = '')
	{
		$user = User::find($id);

		if (!empty($user) && !$user->isActivated() && ($this->is('admin') || $activationCode == $user->activation_code))
		{
			$user->fill(['activated_at' => date('Y-m-d H:i:s')])->save();

			return true;
		}

		return false;
	}

	/**
	 * Email the user based on a specified type.
	 *
	 * @param  object   $user
	 * @param  string   $type
	 * @return boolean
	 */
	public function sendEmail($user, $type)
	{
		foreach (config('auth.email_types') as $view => $subject)
		{
			if ($type == $view)
			{
				$viewLocation = config('auth.views_location').config('auth.views_location_email').'.';

				Mail::send($viewLocation.$view, ['user' => $user], function($mail) use ($user, $subject)
				{
					$mail
						->to($user->email, $user->getName())
						->subject($subject);
				});

				return true;
			}
		}

		return true;
	}

}