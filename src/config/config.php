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

	/*
	|--------------------------------------------------------------------------
	| Layout
	|--------------------------------------------------------------------------
	|
	| The location of your forum view layout. It is defaulted to
	| "open-forum::layouts.master" to use OpenForum's built-in view layout,
	| but you may point it towards a directory of your own for full layout
	| customization.
	|
	*/
	'layout'                   => 'identify::layouts.email',

	/*
	|--------------------------------------------------------------------------
	| Layout Section
	|--------------------------------------------------------------------------
	|
	| The name of the layout section the the email content should appear in.
	|
	*/
	'section'                  => 'content',

	/*
	|--------------------------------------------------------------------------
	| Views Location
	|--------------------------------------------------------------------------
	|
	| The location of your email views. It is defaulted to "identify::" to
	| use Identify's built-in email views, but you may point it towards a views
	| directory of your own for full view customization.
	|
	*/
	'viewsLocation'            => 'identify::',

	/*
	|--------------------------------------------------------------------------
	| Views Location - Email Directory Addition
	|--------------------------------------------------------------------------
	|
	| The location of the email views relative to the specified Views Location.
	| By default "emails" is used meaning the emails view will be in
	| "identify::emails/view". If you do not use "identify::" as your Views
	| Location, you may want to use "emails/auth". You do not have to worry
	| about the trailing slash. Identify will take care of that for you.
	|
	*/
	'viewsLocationEmail'       => 'emails',

	/*
	|--------------------------------------------------------------------------
	| Email Types Setup
	|--------------------------------------------------------------------------
	|
	| The data setup for creating and updating a user. You may create different
	| types for different situations. There are 2 types already here by
	| default which are "standard" and "password". This data is
	| held in the config file so composer update won't overwrite your custom
	| settings that will vary based on your users table setup.
	|
	*/
	'emailTypes' => array(
		'Signup Confirmation' => 'signup_confirmation',
		'Activation'          => 'activation',
		'Activated'           => 'activated',
		'Reset Password'      => 'reset_password',
	),

);