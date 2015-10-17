<?php namespace Regulus\Identify\Libraries;

class Router extends \Illuminate\Routing\Router {

	public function resolveRouteFromUrl($url)
	{
		try {
			return $this->findRoute(\Illuminate\Http\Request::create($url));
		}
		catch (\Exception $e)
		{
			return null;
		}
	}

}