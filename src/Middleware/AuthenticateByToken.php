<?php namespace Regulus\Identify\Middleware;

use Auth;
use Closure;
use Hash;

class AuthenticateByToken {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$token = $request->header('api-token');

		if (is_null($token))
			$token = $request->get('api_token');

		if (!Auth::check() && !is_null($token) && $token != "")
		{
			$token = explode(':', $token);

			if (count($token) == 2)
			{
				$user = Auth::getProvider()->createModel()->find($token[0]);

				if ($user && $user->checkApiToken($token[1]))
				{
					Auth::login($user, false, true);
				}
			}
		}

		return $next($request);
	}

}