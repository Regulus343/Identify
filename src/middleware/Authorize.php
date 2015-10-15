<?php namespace Regulus\Identify\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

use Illuminate\Support\Facades\Config;

class Authorize {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$routes = config('auth.routes');

		$baseUrl = str_replace('http://', '', str_replace('https://', '', str_replace('www.', '', config('app.url'))));

		$routeUri = $request->route()->getUri();
		$routeAction = $request->route()->getAction();

		if (!isset($routeAction['as']))
			return $next($request);

		$routeName = $routeAction['as'];

		$permissions = null;

		if (isset($routes[$routeName]))
		{
			$permissions = $this->formatPermissions($routes[$routeName]);
		}
		else
		{
			$routeNameArray = explode('.', $routeName);

			for ($r = (count($routeNameArray) - 2); $r >= 0; $r--)
			{
				if (is_null($permissions))
				{
					$routeNamePartial = "";

					for ($a = 0; $a <= $r; $a++)
					{
						if ($routeNamePartial != "")
							$routeNamePartial .= ".";

						$routeNamePartial .= $routeNameArray[$a];
					}

					$routeNamePartial .= ".*";

					if (isset($routes[$routeNamePartial]))
					{
						$routeName = $routeNamePartial;

						$permissions = $this->formatPermissions($routes[$routeName]);
					}
				}
			}
		}

		if (!is_null($permissions))
		{
			$authorized = true;

			$allPermissionsRequired = in_array('[ALL]', $permissions);

			if ($allPermissionsRequired)
			{
				foreach ($permissions as $p => $permission)
				{
					if ($permission == "[ALL]")
						unset($permissions[$p]);
				}

				$authorized = $this->auth->hasPermissions($permissions);
			}
			else
			{
				$authorized = $this->auth->hasPermission($permissions);
			}

			Config::set('auth.unauthorized_route.name', $routeName);
			Config::set('auth.unauthorized_route.permissions', $permissions);
			Config::set('auth.unauthorized_route.all_permissions_required', $allPermissionsRequired);

			if (!$authorized)
				abort(config('auth.unauthorized_error_code'));
		}

		return $next($request);
	}

	private function formatPermissions($permissions)
	{
		if (is_string($permissions))
			$permissions = [$permissions];

		return $permissions;
	}

}