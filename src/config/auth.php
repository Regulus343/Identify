<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Authentication Defaults
	|--------------------------------------------------------------------------
	|
	| This option controls the default authentication "guard" and password
	| reset options for your application. You may change these defaults
	| as required, but they're a perfect start for most applications.
	|
	*/

	'defaults' => [
		'guard'     => 'web',
		'passwords' => 'users',
	],

	/*
	|--------------------------------------------------------------------------
	| Authentication Guards
	|--------------------------------------------------------------------------
	|
	| Next, you may define every authentication guard for your application.
	| Of course, a great default configuration has been defined for you
	| here which uses session storage and the Eloquent user provider.
	|
	| All authentication drivers have a user provider. This defines how the
	| users are actually retrieved out of your database or other storage
	| mechanisms used by this application to persist your user's data.
	|
	| Supported: "session", "token"
	|
	*/

	'guards' => [
		'web' => [
			'driver'   => 'session',
			'provider' => 'users',
		],

		'api' => [
			'driver'   => 'token',
			'provider' => 'users',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| User Providers
	|--------------------------------------------------------------------------
	|
	| All authentication drivers have a user provider. This defines how the
	| users are actually retrieved out of your database or other storage
	| mechanisms used by this application to persist your user's data.
	|
	| If you have multiple user tables or models you may configure multiple
	| sources which represent each model / table. These sources may then
	| be assigned to any extra authentication guards you have defined.
	|
	| Supported: "database", "eloquent"
	|
	*/

	'providers' => [
		'users' => [
			'driver' => 'eloquent',
			'model'  => Regulus\Identify\Models\User::class,
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Resetting Passwords
	|--------------------------------------------------------------------------
	|
	| Here you may set the options for resetting passwords including the view
	| that is your password reset e-mail. You may also set the name of the
	| table that maintains all of the reset tokens for your application.
	|
	| You may specify multiple password reset configurations if you have more
	| than one user table or model in the application and you want to have
	| separate password reset settings based on the specific user types.
	|
	| The expire time is the number of minutes that the reset token should be
	| considered valid. This security feature keeps tokens short-lived so
	| they have less time to be guessed. You may change this as needed.
	|
	*/

	'passwords' => [
		'users' => [
			'provider' => 'users',
			'email'    => 'auth.emails.password',
			'table'    => 'password_resets',
			'expire'   => 60,
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Tables Prefix
	|--------------------------------------------------------------------------
	|
	| The prefix for authentication tables. You may use false, null, or blank
	| to avoid using a prefix, but it is recommended you use one to keep all of
	| your authentication / authorization tables together.
	|
	*/

	'tables_prefix' => 'auth',

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
	| email address at the same time.
	|
	*/

	'username' => [
		'field'        => 'username',
		'allow_spaces' => false,
	],

	'log_in_username_or_email' => true,

	/*
	|--------------------------------------------------------------------------
	| API Tokens
	|--------------------------------------------------------------------------
	|
	| Max API tokens will allow you to set a max number of authenticatable
	| API tokens per user. SEt it to 1 to just use the "api_token" column in
	| the users table. The default is 6 and therefore will use the
	| "auth_api_tokens" table and allow up to 6 devices to be simultaneously
	| signed in. For the mobile lifetime, you can set it to false to use the
	| same lifetime as for non-mobile devices (user agent is used to determine
	| if a mobile device is being authenticated).
	|
	*/

	'api_tokens' => [
		'max'             => 6,
		'lifetime'        => 1440 * 30, // lifetime in minutes (default is 30 days)
		'lifetime_mobile' => null, // defaults to perpetual sessions for mobile
	],

	/*
	|--------------------------------------------------------------------------
	| Enable Access Level
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
	| State Defaults
	|--------------------------------------------------------------------------
	|
	| The default values for user state data items. You may also use the keys
	| to validate whether items should be able to be set if you set these with
	| a generalized API function.
	|
	*/

	'state_defaults' => [

		// 'some_key' => 'Default Value',

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
	| An array of views for the different user-related email types.
	|
	*/

	'email_types' => [
		'confirmation',
		'password',
		'banned',
		'closed',
	],

	/*
	|--------------------------------------------------------------------------
	| App Name Email Subject Prefix
	|--------------------------------------------------------------------------
	|
	| Whether to prefix all email subjects with the app name.
	|
	*/

	'app_name_email_subject_prefix' => true,

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