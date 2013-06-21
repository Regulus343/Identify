<?php namespace Regulus\Identify;

/*----------------------------------------------------------------------------------------------------------
	Identify
		A composer package that adds roles to Laravel 4's basic authentication/authorization.

		created by Cody Jassman
		last updated on May 19, 2013
----------------------------------------------------------------------------------------------------------*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

use Regulus\TetraText\TetraText as Format;

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
		return ! static::is($roles);
	}

	/**
	 * Redirect to a specified page with an error message. A default message is supplied if a custom message not set.
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