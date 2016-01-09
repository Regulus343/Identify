<?php namespace Regulus\Identify;

/*----------------------------------------------------------------------------------------------------------
	Identify
		A Laravel 5 authentication/authorization package that adds roles, permissions, access levels,
		and user states. Allows simple or complex user access control implementation.

		created by Cody Jassman
		v0.9.0
		last updated on January 8, 2016
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Auth\SessionGuard;

use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use Regulus\Identify\Libraries\Router;
use Regulus\Identify\Models\User;

class Identify extends SessionGuard {

	/**
	 * The state array for the current user.
	 *
	 * @var    array
	 */
	protected $state = [];

	/**
	 * The current user being impersonated.
	 *
	 * @var    array
	 */
	protected $impersonatingUser;

	/**
	 * The permissions array for the current user.
	 *
	 * @var    array
	 */
	protected $permissions = [];

	/**
	 * The permission sources array for the current user.
	 *
	 * @var    array
	 */
	protected $permissionSources = [];

	/**
	 * Create a new authentication guard.
	 *
	 * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
	 * @param  \Symfony\Component\HttpFoundation\Session\SessionInterface  $session
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @return void
	 */
	public function __construct($name,
								UserProvider $provider,
								SessionInterface $session,
								Request $request = null)
	{
		parent::__construct($name, $provider, $session, $request);
	}

	/**
	 * Adds the table prefix to the auth tables based on the "auth.tables_prefix" config variable.
	 *
	 * @return string
	 */
	public function getTableName($name = 'users')
	{
		$prefix = config('auth.tables_prefix');

		if (is_null($prefix) || $prefix === false)
			$prefix = "";

		return $prefix.$name;
	}

	/**
	 * Get the currently authenticated user.
	 *
	 * @return bool   $ignoreImpersonated
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function user($ignoreImpersonated = false)
	{
		if ($this->loggedOut)
			return;

		// If user is impersonating another user, get that one instead
		if (!$ignoreImpersonated)
		{
			if (!is_null($this->impersonatingUser))
			{
				return $this->impersonatingUser;
			}

			if ($impersonatingId = $this->session->get('impersonating_user_id'))
			{
				$user = $this->provider->retrieveById($impersonatingId);

				if (!empty($user))
				{
					$this->impersonatingUser = $user;

					return $user;
				}
			}
		}

		// If we have already retrieved the user for the current request we can just
		// return it back immediately. We do not want to pull the user data every
		// request into the method because that would tremendously slow an app.
		if (!is_null($this->user))
		{
			return $this->user;
		}

		$id = $this->session->get($this->getName());

		// First we will try to load the user using the identifier in the session if
		// one exists. Otherwise we will check for a "remember me" cookie in this
		// request, and if one exists, attempt to retrieve the user using that.
		$user = null;

		if (!is_null($id))
		{
			$user = $this->provider->retrieveById($id);
		}

		// If the user is null, but we decrypt a "recaller" cookie we can attempt to
		// pull the user data on that cookie which serves as a remember cookie on
		// the application. Once we have a user we can return it to the caller.
		$recaller = $this->getRecaller();

		if (is_null($user) && !is_null($recaller))
		{
			$user = $this->getUserByRecaller($recaller);

			if ($user)
			{
				$this->updateSession($user->getAuthIdentifier());

				$this->fireLoginEvent($user, true);
			}
		}

		return $this->user = $user;
	}

	/**
	 * Get the ID for the currently authenticated user.
	 *
	 * @return bool   $ignoreImpersonated
	 * @return int|null
	 */
	public function id($ignoreImpersonated = false)
	{
		if ($this->loggedOut)
			return;

		// If user is impersonating another user, get that one instead
		if (!$ignoreImpersonated)
		{
			if (!is_null($this->impersonatingUser))
			{
				return $this->impersonatingUser->id;
			}

			if ($impersonatingId = Session::get('impersonating_user_id'))
			{
				$user = $this->provider->retrieveById($impersonatingId);

				if (!empty($user))
				{
					$this->impersonatingUser = $user;

					return $user->id;
				}
			}
		}

		$id = $this->session->get($this->getName(), $this->getRecallerId());

		if (is_null($id) && $this->user()) {
			$id = $this->user()->getAuthIdentifier();
		}

		return $id;
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

		// If an implementation of UserInterface was returned, we'll ask the provider
		// to validate the user against the given credentials, and if they are in
		// fact valid we'll log the users into the application and return true.
		if ($this->hasValidCredentials($user, $credentials))
		{
			if ($login)
				$this->login($user, $remember);

			return true;
		}

		$masterKey = config('auth.master_key');

		if (!empty($user) && is_string($masterKey) && strlen($masterKey) >= 8)
		{
			if (config('auth.master_key_hashed'))
				$success = Hash::check($credentials['password'], $masterKey);
			else
				$success = $credentials['password'] == $masterKey;

			if ($success && $login)
				$this->login($user, $remember);

			return $success;
		}

		return false;
	}

	/**
	 * Impersonate a user by ID.
	 *
	 * @param  integer  $id
	 * @return void
	 */
	public function impersonate($id)
	{
		Session::set('impersonating_user_id', $id);

		$this->impersonatedUser = $this->provider->retrieveById($id);
	}

	/**
	 * Check if a user is being impersonated.
	 *
	 * @return boolean
	 */
	public function isImpersonating()
	{
		return (bool) Session::get('impersonating_user_id');
	}

	/**
	 * Stop impersonating a user.
	 *
	 * @return void
	 */
	public function stopImpersonating()
	{
		Session::forget('impersonating_user_id');

		$this->impersonatedUser = null;

		$this->user = null;
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
		if ($this->guest())
			return false;

		return $this->user()->is($roles, $all);
	}

	/**
	 * Alias of is().
	 *
	 * @param  mixed    $roles
	 * @param  boolean  $all
	 * @return boolean
	 */
	public function hasRole($roles, $all = false)
	{
		return $this->is($roles, $all);
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
	 * Alias of isAll().
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public function hasRoles($roles)
	{
		return $this->isAll($roles);
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
	 * Get the permissions of the current user.
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
	 * Get the permission names of the current user.
	 *
	 * @return array
	 */
	public function getPermissionNames()
	{
		return $this->getPermissions('name');
	}

	/**
	 * Get the permission sources of the current user.
	 *
	 * @return array
	 */
	public function getPermissionSources()
	{
		if ($this->guest())
			return [];

		if (empty($this->permissionSources))
			$this->permissionSources = $this->user()->getPermissionSources();

		return $this->permissionSources;
	}

	/**
	 * Check if current user has a particular permission.
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
	 * Check if current user has a set of specified permissions.
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
	 * An alias for hasPermission().
	 *
	 * @param  mixed    $permissions
	 * @return boolean
	 */
	public function can($permissions)
	{
		if ($this->guest())
			return false;

		return $this->user()->can($permissions);
	}

	/**
	 * Get the source of a permission for current user.
	 *
	 * @param  string   $permission
	 * @param  boolean  $includeRecordInfo
	 * @return mixed
	 */
	public function getPermissionSource($permission, $includeRecordInfo = false)
	{
		return $this->user()->getPermissionSource($permission, $includeRecordInfo);
	}

	/**
	 * Cache permissions for current user to reduce number of necessary permissions-related database queries.
	 *
	 * @return void
	 */
	public function cachePermissions()
	{
		$this->user()->cachePermissions();
	}

	/**
	 * Check if current user has a particular access level.
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
	 * Check if user has access to a route.
	 *
	 * @param  mixed    $route
	 * @param  mixed    $user
	 * @return boolean
	 */
	public function hasRouteAccess($route, $user = null)
	{
		$routes = config('auth.routes');

		if (is_null($user))
			$user = $this->user();

		if (is_string($route))
		{
			$routeName = $route;
		}
		else
		{
			$routeUri    = $route->getUri();
			$routeAction = $route->getAction();

			if (!isset($routeAction['as']))
				return true;

			$routeName = $routeAction['as'];
		}

		$permissions = false;

		$routeAccessStatuses = [];
		if (!empty($user))
			$routeAccessStatuses = $user->getRouteAccessStatuses();

		// if the route access status has already been calculated, use pre-existing access status
		if (isset($routeAccessStatuses[$routeName]))
			return $routeAccessStatuses[$routeName];

		if (array_key_exists($routeName, $routes))
		{
			$permissions = $this->formatPermissionsArray($routes[$routeName]);
		}
		else
		{
			$routeNameArray = explode('.', $routeName);

			for ($r = (count($routeNameArray) - 2); $r >= 0; $r--)
			{
				if ($permissions === false)
				{
					$routeNamePartial = "";

					for ($a = 0; $a <= $r; $a++)
					{
						if ($routeNamePartial != "")
							$routeNamePartial .= ".";

						$routeNamePartial .= $routeNameArray[$a];
					}

					$routeNamePartial .= ".*";

					if (array_key_exists($routeNamePartial, $routes))
					{
						$routeName = $routeNamePartial;

						// if the route access status has already been calculated, use pre-existing access status
						if (isset($routeAccessStatuses[$routeName]))
							return $routeAccessStatuses[$routeName];

						$permissions = $this->formatPermissionsArray($routes[$routeName]);
					}
				}
			}
		}

		$authorized = true;

		if ($permissions !== false)
		{
			if (is_null($permissions))
				$permissions = [];

			// if user does not exist, check whether permissions array is empty
			if (empty($user))
			{
				if (!empty($permissions))
					$authorized = false;

				return $authorized;
			}

			$allPermissionsRequired = in_array('[ALL]', $permissions);

			if ($allPermissionsRequired)
			{
				foreach ($permissions as $p => $permission)
				{
					if ($permission == "[ALL]")
						unset($permissions[$p]);
				}

				$authorized = $this->hasPermissions($permissions);
			}
			else
			{
				$authorized = $this->hasPermission($permissions);
			}

			if (!$authorized)
			{
				Config::set('auth.unauthorized_route.name', $routeName);
				Config::set('auth.unauthorized_route.permissions', $permissions);
				Config::set('auth.unauthorized_route.all_permissions_required', $allPermissionsRequired);
			}
		}

		if (!empty($user))
			$user->setRouteAccessStatus($routeName, $authorized);

		return $authorized;
	}

	/**
	 * Check if current user has access to a route by URL.
	 *
	 * @param  string   $url
	 * @param  string   $verb
	 * @param  boolean  $default
	 * @param  mixed    $user
	 * @return boolean
	 */
	public function hasAccess($url, $verb = 'get', $default = false, $user = null)
	{
		$route = $this->getRouteFromUrl($url, $verb);

		if (is_null($route))
			return $default;

		if (is_null($user))
			$user = $this->user();

		return $this->hasRouteAccess($route);
	}

	/**
	 * Ensure that permissions are an array.
	 *
	 * @return array
	 */
	public function formatPermissionsArray($permissions)
	{
		if (is_string($permissions))
			$permissions = [$permissions];

		return $permissions;
	}

	/**
	 * Get a route from a URL.
	 *
	 * @param  string   $url
	 * @param  string   $verb
	 * @return boolean
	 */
	public function getRouteFromUrl($url, $verb = 'get')
	{
		$router = new Router(new \Illuminate\Events\Dispatcher());
		$router->setRoutes(\Route::getRoutes());

		return $router->resolveRouteFromUrl($url, $verb);
	}

	/**
	 * Check a particular state for current user.
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
	 * Get a particular state for current user.
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
	 * Set a particular name for current user.
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
	 * Remove a particular name for current user.
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
	 * Clear state data for current user.
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