<?php namespace Regulus\Identify;

/*----------------------------------------------------------------------------------------------------------
	Identify
		A composer package that adds roles to Laravel 4's basic authentication/authorization.

		created by Cody Jassman
		last updated on February 3, 2013
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Auth;

class Identify extends Auth {

	/**
	 * Checks whether the user is in one of the given roles ($roles can be an array of roles or a string of a single role).
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public static function allow($roles)
	{
		$allowed = false;
		$userRoles = Auth::user()->roles;
		if (is_array($roles)) {
			foreach ($userRoles as $userRole) {
				foreach ($roles as $role) {
					if (strtolower($userRole->name) == strtolower($role)) $allowed = true;
				}
			}
		} else {
			$role = $roles;
			foreach ($userRoles as $userRole) {
				if (strtolower($userRole->name) == strtolower($role)) $allowed = true;
			}
		}
		return $allowed;
	}

	/**
	 * A simple inversion of the allow() method to check if a user should be denied access to the subsequent content.
	 *
	 * @param  mixed    $roles
	 * @return boolean
	 */
	public static function deny($roles)
	{
		return ! static::allow($roles);
	}

}