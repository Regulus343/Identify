Identify
========

**A Laravel 5 authentication/authorization package that adds roles, permissions, access levels, and user states. Allows simple or complex user access control implementation.**

[![Latest Stable Version](https://poser.pugx.org/regulus/identify/v/stable.svg)](https://packagist.org/packages/regulus/identify) [![License](https://poser.pugx.org/regulus/identify/license.svg)](https://packagist.org/packages/regulus/identify)

> **Note:** For Laravel 4, you may use <a href="https://github.com/Regulus343/Identify/tree/v0.4.3">version 0.4.3</a>.

- [Composer Package Installation](#composer-package-installation)
- [Installation](#installation)
- [Basic Usage](#basic-usage)

<a name="composer-package-installation"></a>
## Composer Package Installation

To install Identify, make sure "regulus/identify" has been added to Laravel 5's `composer.json` file.

	"require": {
		"regulus/identify": "0.8.*"
	},

Then run `php composer.phar update` from the command line. Composer will install the Identify package.

<a name="installation"></a>
## Installation

**Register service provider and set up alias:**

Add this to the `providers` array in `config/app.php`:

	'Regulus\Identify\IdentifyServiceProvider',

And add this to the `aliases` array:

	'Auth' => 'Regulus\Identify\Facade',

**Add middleware to the `routeMiddleware` array in `app/Http/Kernal.php`:**

	'auth.permissions' => 'Regulus\Identify\Middleware\Authorize',

**Add and run the install command:**

Add the following to the `commands` array in `app/Console/Kernel.php`:

	'Regulus\Identify\Commands\Install',
	'Regulus\Identify\Commands\CreateUser',

Then run the following command:

	php artisan identify:install

Identify will now be installed. This includes all necessary DB migrations, DB seeding, and config publishing. The config file that is published is `auth.php` and will overwrite Laravel 5's default auth configuration. The default table names are prefixed with `auth_`, but you may alter the users table name (from which the other table names are derived) by adding a `--table` option to the install line:

	php artisan identify:install --table=users

	php artisan identify:install --table=identify_users

The former example will remove the prefix from all of the table names, so you will get `users`, `roles`, etc. The latter example will change the default table prefix of `auth_` to `identify_` so your table names will be `identify_users`, `identify_roles`, etc.

You should now have 4 users, `Admin`, `TestUser`, `TestUser2`, and `TestUser3`. All of the default passwords are simply `password` and the usernames are case insensitive, so you may simply type `admin` and `password` to log in. The 3 initial roles are `Administrator`, `Moderator`, and `Member`. `Admin` has the `Administrator` role, `TestUser` has the `Moderator` role, and the final 2 users have the `Member` role.

You may now skip ahead to the [Basic Usage](#basic-usage) section.

<a name="basic-usage"></a>
## Basic Usage

**Checking whether a user is logged in:**

	if (Auth::check())
	{
		// the user is logged in
	}

**Checking whether a user has a particular role:**

	if (Auth::is('admin'))
	{
		// the user has an "admin" role
	}

	if (Auth::is(['admin', 'user']))
	{
		// the user has an "admin" role and/or a "user" role
	}

**Checking whether a user does not have a particular role:**

	if (Auth::isNot('admin'))
	{
		// the user lacks an "admin" role
	}

	if (Auth::isNot(['admin', 'user']))
	{
		// the user lacks the "admin" and "user" roles
	}

**Authorize a specific role or roles:**

	//redirect to "home" URI if the user does not have one of the specified roles
	Auth::authorize(['admin', 'user'], 'home');

	//with a custom message (otherwise a default one is provided)
	Auth::authorize(['admin', 'user'], 'home', 'You are not authorized to access the requested page.');

**Automatically redirect to a URI with the unauthorized message:**

	//redirect to "home" URI if the user does not have one of the specified roles
	return Auth::unauthorized('home');

	//with a custom message (otherwise a default one is provided)
	return Auth::unauthorized('home', 'You are not authorized to access the requested page.');

The third argument is the name of the session variable. The default is 'messages' so if the user is redirected, `Session::get('messages')` will return an array like:

	['error' => 'You are not authorized to access the requested page.']

**Check whether a user has route access based on permissions:**

	if (Auth::hasRouteAccess('pages.edit')
	{
		// user has access to "pages.edit" route
	}

**Check whether a user has route access based on permissions:**

	if (Auth::hasAccess('pages/edit/home')
	{
		// user has access to "pages/edit/home" URI (based on "config/auth.routes.php" route permissions mapping)
	}

**Create a new user account:**

	Auth::createUser();

	//use custom input array
	Auth::createUser([
		'username' => 'TestUser',
		'email'    => 'test@localhost',
		'password' => 'password',
		'role_id'  => 2,
	]);

	//automatically activate user account
	Auth::createUser($input, true);

	//suppress confirmation email
	Auth::createUser($input, true, false);

**Create a new user account via the command line interface:**

	//use default password of "password"
	php artisan user:create username email@address.com

	//use alternate password
	php artisan user:create username email@address.com --password=anotherpassword

	//automatically activate user
	php artisan user:create username email@address.com --activate

	//automatically activate user and suppress confirmation email
	php artisan user:create username email@address.com --activate --suppress

**Send an email to a user with a specific view in `views/emails`:**

	Auth::sendEmail($user, 'confirmation');

	Auth::sendEmail($user, 'banned');

	Auth::sendEmail($user, 'deleted');

	Auth::sendEmail($user, 'password');

**Activate a user account by ID and activation token:**

	if (Auth::activate(1, 'wHHhONhavZps1J9p8Rs6WIXsTK30tFhl'))
	{
		//user ID #1 has been activated
	}