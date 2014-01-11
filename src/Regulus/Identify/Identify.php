<?php namespace Regulus\Identify;

/*----------------------------------------------------------------------------------------------------------
	Identify
		A composer package that adds roles to Laravel 4's basic authentication/authorization.

		created by Cody Jassman
		last updated on January 9, 2014
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

use Regulus\Identify\User as User;
use Regulus\TetraText\TetraText as Format;

class Identify extends Auth {

	/**
	 * Returns the active user ID for the session, or 0 if the user is not logged in.
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public static function userId()
	{
		if (!static::guest()) {
			return Auth::user()->id;
		}
		return 0;
	}

	/**
	 * Attempt to authenticate a user using the given credentials.
	 *
	 * @param  array  $credentials
	 * @param  bool   $remember
	 * @param  bool   $login
	 * @return bool
	 */
	public static function attempt(array $credentials = array(), $remember = false, $login = true)
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
	 * Checks whether the user is in one of the given roles ($roles can be an array of roles or a string of a single role).
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public static function is($roles)
	{
		$allowed = false;
		if (Auth::check()) {
			$userRoles = Auth::user()->roles;
			if (is_array($roles)) {
				foreach ($userRoles as $userRole) {
					foreach ($roles as $role) {
						if (strtolower($userRole->role) == strtolower($role))
							$allowed = true;
					}
				}
			} else {
				$role = $roles;
				foreach ($userRoles as $userRole) {
					if (strtolower($userRole->role) == strtolower($role))
						$allowed = true;
				}
			}
		}
		return $allowed;
	}

	/**
	 * A simple inversion of the is() method to check if a user should be denied access to the subsequent content.
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public static function isNot($roles)
	{
		return ! static::is($roles);
	}

	/**
	 * Redirect to a specified page with an error message. A default message is supplied if a custom message not set.
	 *
	 * @param  string   $uri
	 * @param  mixed    $message
	 * @param  string   $messageVar
	 * @return boolean
	 */
	public static function unauthorized($uri = '', $message = false, $messagesVar = 'messages')
	{
		if (!$message) $message = Lang::get('identify::messages.unauthorized');
		return Redirect::to($uri)->with($messagesVar, array('error' => $message));
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
	public static function authorize($roles, $uri = '', $message = false, $messagesVar = 'messages')
	{
		if (static::isNot($roles))
			return static::unauthorized($uri, $message, $messagesVar);

		return false;
	}

	/**
	 * Redirect to a specified page with an error message. A default message is supplied if a custom message not set.
	 *
	 * @param  array    $routeFilters
	 * @param  boolean  $includeSubRoutes
	 * @return boolean
	 */
	public static function setRouteFilters($routeFilters = array(), $includeSubRoutes = true)
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
	 * Attempt to activate a user account by the user ID and activation code.
	 *
	 * @param  integer  $id
	 * @param  string   $activationCode
	 * @return boolean
	 */
	public static function activate($id = 0, $activationCode = '')
	{
		$user = User::find($id);
		if (!empty($user) && !$user->active && (static::is('admin') || $activationCode == $user->activation_code)) {
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
	 * @param  integer  $id
	 * @param  string   $type
	 * @return boolean
	 */
	public static function sendEmail($user, $type)
	{
		foreach (Config::get('identify::emailTypes') as $view => $subject) {
			if ($type == $view) {
				$viewLocation = Config::get('identify::viewsLocation').Config::get('identify::viewsLocationEmail').'.';
				Mail::send($viewLocation.$view, array('user' => $user), function($m) use ($user, $subject)
				{
					$m->to($user->email, $user->getName())->subject($subject);
				});
				return true;
			}
		}
		return true;
	}

}
