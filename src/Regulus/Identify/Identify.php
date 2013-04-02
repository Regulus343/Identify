<?php namespace Regulus\Identify;

/*----------------------------------------------------------------------------------------------------------
	Identify
		A composer package that adds roles to Laravel 4's basic authentication/authorization.

		created by Cody Jassman
		last updated on February 3, 2013
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;

class Identify extends Auth {

	/**
	 * Returns the active user ID for the session, or 0 if the user is not logged in.
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public static function userID()
	{
		if (!static::guest()) {
			return Auth::user()->id;
		}
		return 0;
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
						if (strtolower($userRole->role) == strtolower($role)) $allowed = true;
					}
				}
			} else {
				$role = $roles;
				foreach ($userRoles as $userRole) {
					if (strtolower($userRole->role) == strtolower($role)) $allowed = true;
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
		return ! static::allow($roles);
	}

	/**
	 * Redirect to a specified page with an error message. A default message is supplied if a custom message
	 * not set.
	 *
	 * @param  string   $uri
	 * @param  mixed    $message
	 * @return boolean
	 */
	public static function unauthorized($uri, $message = false)
	{
		if (!$message) $message = Lang::get('identify::messages.unauthorized');
		return Redirect::to($uri)->with('messageError', $message);
	}

}