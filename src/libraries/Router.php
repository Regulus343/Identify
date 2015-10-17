<?php namespace Regulus\Identify\Libraries;

class Router extends \Illuminate\Routing\Router {

	/**
	 * Get a route name from a URL.
	 *
	 * @param  string   $url
	 * @param  string   $verb
	 * @return boolean
	 */
	public function resolveRouteFromUrl($url, $verb = 'get')
	{
		try
		{
			$verb = strtoupper($verb);

			$request = \Illuminate\Http\Request::create($url, $verb);

			return $this->findRoute($request);
		}
		catch (\Exception $e) // prevent exception from breaking app when checking access permissions
		{
			return null;
		}
	}

}