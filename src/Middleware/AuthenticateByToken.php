<?php namespace Regulus\Identify\Middleware;

use Auth;
use Closure;

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
			$user = Auth::getProvider()->createModel()->where('api_token', $token)->first();

			if ($user)
				Auth::login($user, false, true);
		}

		return $next($request);
	}

}