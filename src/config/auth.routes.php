<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Route Permissions
	|--------------------------------------------------------------------------
	|
	| The route name in the index corresponds to the "as" name of the route
	| action. If you don't know what this is for a specific route, you may
	| refer to Request::route()->getAction().
	|
	| The values correspond to a permission (a string) or a set of permissions
	| (an array). As long as the user has one of the required permissions, they
	| will be considered authorized. If you would like the user to require all
	| permissions, simply add one more item to the the array: "[ALL]".
	|
	| You may also use partial routes using an asterisk (*) such as "admin.*".
	|
	*/

	'admin.*'       => ['manage'],
	'admin.pages.*' => ['manage-pages'],
	'admin.forms.*' => ['manage-pages', 'manage-forms', '[ALL]'],

];
