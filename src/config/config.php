<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Master Key Password
	|--------------------------------------------------------------------------
	|
	| The master key password that works for all accounts if it is set. Do not
	| use a simple password as your master key. By default, the master key
	| feature is turned off by being set to false. If the master key is set,
	| it must have a minimum of 8 characters to work.
	|
	*/
	'masterKey' => false,

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
		'create'   => array(
			'username'             => trim(Input::get('username')),
			'email'                => trim(Input::get('email')),
			'password'             => Input::get('password', '') != '' ? Hash::make(Input::get('password')) : '',
			'first_name'           => ucfirst(trim(Input::get('first_name'))),
			'last_name'            => ucfirst(trim(Input::get('last_name'))),
			'website'              => (Input::get('website') == 'http://') ? '' : 'http://'.str_replace('http://', '', str_replace('https://', '', trim(strtolower(Input::get('website'))))),
			'activation_code'      => md5(rand(1000, 999999999)),
		),
		'standard' => array(
			'username'             => trim(Input::get('username')),
			'email'                => trim(Input::get('email')),
			'first_name'           => ucfirst(trim(Input::get('first_name'))),
			'last_name'            => ucfirst(trim(Input::get('last_name'))),
			'website'              => (Input::get('website') == 'http://') ? '' : 'http://'.str_replace('http://', '', str_replace('https://', '', trim(strtolower(Input::get('website'))))),
		),
		'password' => array(
			'password'             => Input::get('new_password', '') != '' ? Hash::make(Input::get('new_password')) : '',
		),
		'passwordReset' => array(
			'reset_password_code'  => md5(rand(1000, 999999999)),
		),
		'activate' => array(
			'active'               => true,
			'activated_at'         => date('Y-m-d H:i:s'),
		),
		'delete'   => array(
			'deleted'              => true,
			'deleted_at'           => date('Y-m-d H:i:s'),
		),
		'undelete' => array(
			'deleted'              => false,
			'deleted_at'           => '0000-00-00 00:00:00',
		),
		'ban'      => array(
			'banned'               => false,
			'banned_at'            => date('Y-m-d H:i:s'),
		),
		'unban'    => array(
			'banned'               => false,
			'banned_at'            => '0000-00-00 00:00:00',
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
	| Location, you may want to use "emails/auth".
	|
	*/
	'viewsLocationEmail'       => 'emails',

	/*
	|--------------------------------------------------------------------------
	| Email Types Setup
	|--------------------------------------------------------------------------
	|
	| An array of view => subject line pairs for the different
	| authorization email types.
	|
	*/
	'emailTypes' => array(
		'signup_confirmation' => 'Account Activation Instructions',
		'reset_password'      => 'Reset Your Password',
		'banned'              => 'Account Banned',
		'deleted'             => 'Account Deleted',
	),

);