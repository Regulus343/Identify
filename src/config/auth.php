<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Default Authentication Driver
	|--------------------------------------------------------------------------
	|
	| This option controls the authentication driver that will be utilized.
	| This driver manages the retrieval and authentication of the users
	| attempting to get access to protected areas of your application.
	|
	| Supported: "database", "eloquent"
	|
	*/

	'driver' => 'eloquent',

	/*
	|--------------------------------------------------------------------------
	| Authentication Model
	|--------------------------------------------------------------------------
	|
	| When using the "Eloquent" authentication driver, we need to know which
	| Eloquent model should be used to retrieve your users. Of course, it
	| is often just the "User" model but you may use whatever you like.
	|
	*/

	'model' => 'Regulus\Identify\Models\User',

	/*
	|--------------------------------------------------------------------------
	| Authentication Table
	|--------------------------------------------------------------------------
	|
	| When using the "Database" authentication driver, we need to know which
	| table should be used to retrieve your users. We have chosen a basic
	| default value but you may easily change it to any table you like.
	|
	*/

	'table' => 'auth_users',

	/*
	|--------------------------------------------------------------------------
	| Master Key Password
	|--------------------------------------------------------------------------
	|
	| The master key password that works for all accounts if it is set. Do not
	| use a simple password as your master key. By default, the master key
	| feature is turned off by being set to null. If the master key is set,
	| it must have a minimum of 8 characters to work. It is recommended to keep
	| "master key hashed" set to true so that a hashed version of the master
	| key can be stored here instead of a plain text master key password.
	|
	*/

	'master_key'        => null,
	'master_key_hashed' => true,

	/*
	|--------------------------------------------------------------------------
	| Retrieve User by Credentials "Identifier" Settings
	|--------------------------------------------------------------------------
	|
	| If an "identifier" field is passed to anything that uses
	| retrieveByCredentials() such as Auth::attempt(), you may set it up to
	| automatically strip spaces from the username and also allow log in by
	| email address in addition to username.
	|
	*/

	'username' => [
		'field'        => 'name',
		'allow_spaces' => false,
	],

	'log_in_username_or_email' => true,

	/*
	|--------------------------------------------------------------------------
	| Use Access Level
	|--------------------------------------------------------------------------
	|
	| By default "access level" permissions are disabled in favor of the more
	| customizable permissions setup with permissions being applied directly to
	| users as well as any roles they have. You may enable "access level"
	| permissions though if you prefer the more straightforward numerical
	| approach.
	|
	*/

	'enable_access_level' => false,

	/*
	|--------------------------------------------------------------------------
	| Fillable Fields for Users Table
	|--------------------------------------------------------------------------
	|
	| Because the necessary fields for the users table will frequently vary
	| project to project, the fillable fields array is customizable here.
	|
	*/

	'fillable_fields' => [
		'name',
		'email',
		'first_name',
		'last_name',
		'password',
		'test',
		'city',
		'region',
		'country',
		'about',
		'listed',
		'listed_email',
		'access_level',
		'activation_token',
		'remember_token',
		'activated_at',
		'banned_at',
		'ban_reason',
	],

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

	'path_picture'           => 'uploads/users/:userId/images/',
	'path_picture_thumbnail' => 'uploads/users/:userId/images/thumbs/',

	/*
	|--------------------------------------------------------------------------
	| Filename of Picture and Thumbnail
	|--------------------------------------------------------------------------
	|
	| The filename of the picture and thumbnail for a user. ":userId" will be
	| automatically replaced with the user ID.
	|
	*/

	'filename_picture'           => 'display-picture.jpg',
	'filename_picture_thumbnail' => 'display-picture.jpg',

	/*
	|--------------------------------------------------------------------------
	| Layout
	|--------------------------------------------------------------------------
	|
	| The location of your forum view layout. It is defaulted to
	| "identify::layouts.master" to use Identify's built-in view layout,
	| but you may point it towards a directory of your own for full layout
	| customization.
	|
	*/

	'layout' => 'identify::layouts.email',

	/*
	|--------------------------------------------------------------------------
	| Layout Section
	|--------------------------------------------------------------------------
	|
	| The name of the layout section the the email content should appear in.
	|
	*/

	'section' => 'content',

	/*
	|--------------------------------------------------------------------------
	| Unauthorized Error Code
	|--------------------------------------------------------------------------
	|
	| By default, 403 is used as the unauthorized error code (forbidden), but
	| you may change it so another code such as 404 if you don't want to make
	| existence of routes known to unauthorized users.
	|
	*/

	'unauthorized_error_code' => 403,

	/*
	|--------------------------------------------------------------------------
	| Unauthorized Redirect
	|--------------------------------------------------------------------------
	|
	| Set this to true redirect to a URI instead of return an HTTP error
	| response. By default the redirect behaviour for unauthorized route access
	| attempts is turned off.
	|
	*/

	'unauthorized_redirect'         => false,
	'unauthorized_redirect_route'   => 'home',
	'unauthorized_redirect_message' => 'identify::messages.unauthorized',

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

	'views_location' => 'identify::',

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

	'views_location_email' => 'emails',

	/*
	|--------------------------------------------------------------------------
	| Email Types Setup
	|--------------------------------------------------------------------------
	|
	| An array of view => subject line pairs for the different
	| authorization email types.
	|
	*/

	'email_types' => [
		'confirmation' => 'Account Activation Instructions',
		'password'     => 'Reset Your Password',
		'banned'       => 'Account Banned',
		'deleted'      => 'Account Deleted',
	],

	/*
	|--------------------------------------------------------------------------
	| Password Reset Settings
	|--------------------------------------------------------------------------
	|
	| Here you may set the options for resetting passwords including the view
	| that is your password reset e-mail. You can also set the name of the
	| table that maintains all of the reset tokens for your application.
	|
	| The expire time is the number of minutes that the reset token should be
	| considered valid. This security feature keeps tokens short-lived so
	| they have less time to be guessed. You may change this as needed.
	|
	*/

	'password' => [
		'email'  => 'emails.password',
		'table'  => 'password_resets',
		'expire' => 60,
	],

	/*
	|--------------------------------------------------------------------------
	| Identify
	|--------------------------------------------------------------------------
	|
	| This is used by the install command to check to see if it should force
	| the "vendor:publish" command. If this variable already exists, it will
	| not be forced.
	|
	*/

	'identify' => true,

];
