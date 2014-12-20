<?php namespace Regulus\Identify;

/*----------------------------------------------------------------------------------------------------------
	Identify
		A Laravel authentication/authorization package that adds roles, permissions, access levels,
		and user states and allows simple to complex user access control implementation.

		created by Cody Jassman
		v0.4.3
		last updated on December 20, 2014
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Auth\AuthManager as Auth;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

use Regulus\Identify\User as User;

class Identify extends Auth {

	/**
	 * The user object for the currently logged in user.
	 *
	 * @var    mixed
	 */
	public $user = null;

	/**
	 * The permissions array for the currently logged in user.
	 *
	 * @var    array
	 */
	public $permissions = [];

	/**
	 * The state array for the currently logged in user.
	 *
	 * @var    array
	 */
	public $state = [];

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
	 * Returns the active user for the session.
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public function user()
	{
		if (is_null($this->user))
			$this->user = Auth::user();

		return $this->user;
	}

	/**
	 * Attempt to authenticate a user using the given credentials.
	 *
	 * @param  array    $credentials
	 * @param  boolean  $remember
	 * @param  boolean  $login
	 * @return boolean
	 */
	public function attempt(array $credentials = [], $remember = false, $login = true)
	{
		$masterKey = Config::get('identify::masterKey');
		if (is_string($masterKey) && strlen($masterKey) >= 8 && $credentials['password'] == $masterKey) {
			$user = User::where('username', trim($credentials['username']))->first();
			if ($user) {
				Auth::login($user);
				return true;
			}
		}

		return Auth::attempt($credentials, $remember, $login);
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

		if (Auth::check()) {
			$userRoles = Auth::user()->roles;

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
	 * Attempt to activate a user account by the user ID and activation code.
	 *
	 * @param  integer  $id
	 * @param  string   $activationCode
	 * @return boolean
	 */
	public function activate($id = 0, $activationCode = '')
	{
		$user = User::find($id);
		if (!empty($user) && !$user->active && ($this->is('admin') || $activationCode == $user->activation_code))
		{
			$user->active       = true;
			$user->activated_at = date('Y-m-d H:i:s');
			$user->save();

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
		foreach (Config::get('identify::emailTypes') as $view => $subject) {
			if ($type == $view) {
				$viewLocation = Config::get('identify::viewsLocation').Config::get('identify::viewsLocationEmail').'.';

				Mail::send($viewLocation.$view, ['user' => $user], function($m) use ($user, $subject)
				{
					$m->to($user->email, $user->getName())->subject($subject);
				});

				return true;
			}
		}

		return true;
	}

}