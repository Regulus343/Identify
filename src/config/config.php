<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Table Prefix
	|--------------------------------------------------------------------------
	|
	| The prefix for Identify's table names. The default is "auth_" which means
	| your tables will be "auth_users", "auth_roles", and "auth_user_roles".
	|
	*/
	'tablePrefix' => 'auth_',

	/*
	|--------------------------------------------------------------------------
	| User Create/Update Setup
	|--------------------------------------------------------------------------
	|
	| The data setup for creating and updating a user. You may create different
	| types for different situations. There are 2 types already here by
	| default which are "standard" and "password". This data is
	| held in the config file so composer update won't overwrite your custom
	| settings that will vary based on your users table setup.
	|
	*/
	'dataSetup' => array(
		'standard' => array(
			'username'   => trim(Input::get('username')),
			'email'      => trim(Input::get('email')),
			'first_name' => ucfirst(trim(Input::get('first_name'))),
			'last_name'  => ucfirst(trim(Input::get('last_name'))),
			'website'    => (Input::get('website') == 'http://') ? '' : 'http://'.str_replace('http://', '', trim(strtolower(Input::get('website')))),
		),

		'password' => array(
			'password' => Input::get('password'),
		),
	),

	/*
	|--------------------------------------------------------------------------
	| Path to Picture and Thumbnail Directories
	|--------------------------------------------------------------------------
	|
	| The path to the picture and the thumbnail directories. Ensure that there
	| is a trailing slash on the defined paths. ":userID" will be automatically
	| replaced with the user ID.
	|
	*/
	'pathPicture'          => 'uploads/user_images/:userID/',
	'pathPictureThumbnail' => 'uploads/user_images/thumbs/:userID/',

	/*
	|--------------------------------------------------------------------------
	| Filename of Picture and Thumbnail
	|--------------------------------------------------------------------------
	|
	| The filename of the picture and thumbnail for a user. ":userID" will be
	| automatically replaced with the user ID.
	|
	*/
	'filenamePicture'          => ':userID.jpg',
	'filenamePictureThumbnail' => ':userID.jpg',

);