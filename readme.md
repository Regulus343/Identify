Identify
========

**A Laravel 5 authentication/authorization package that adds roles, permissions, access levels, and user states. Allows simple or complex user access control implementation.**

[![Latest Stable Version](https://poser.pugx.org/regulus/identify/v/stable.svg)](https://packagist.org/packages/regulus/identify) [![License](https://poser.pugx.org/regulus/identify/license.svg)](https://packagist.org/packages/regulus/identify)

- [Composer Package Installation](#composer-package-installation)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Route Permissions](#route-permissions)
- [Creating Accounts and Sending Emails](#accounts-emails)

<a name="composer-package-installation"></a>
## Composer Package Installation

To install Identify, make sure "regulus/identify" has been added to Laravel 5's `composer.json` file.

	"require": {
		"regulus/identify": "0.9.*"
	},

Then run `php composer.phar update` from the command line. Composer will install the Identify package.

<a name="installation"></a>
## Installation

**Register service provider and set up alias:**

Add this to the `providers` array in `config/app.php`:

	Regulus\Identify\IdentifyServiceProvider::class,

And add this to the `aliases` array:

	'Auth' => Regulus\Identify\Facade::class,

**Add middleware to the `routeMiddleware` array in `app/Http/Kernal.php`:**

	'auth.permissions' => \Regulus\Identify\Middleware\Authorize::class,
	'auth.token'       => \Regulus\Identify\Middleware\AuthenticateByToken::class,

**Add and run the install command:**

Add the following to the `commands` array in `app/Console/Kernel.php`:

	\Regulus\Identify\Commands\Install::class,
	\Regulus\Identify\Commands\CreateUser::class,

Then run the following command:

	php artisan identify:install

Identify will now be installed. This includes all necessary DB migrations, DB seeding, and config publishing. The config file that is published is `auth.php` and will overwrite Laravel 5's default auth configuration. The default table names are prefixed with `auth_`, but you may alter the tables prefix by adding a `--tables-prefix` option to the install line:

	php artisan identify:install --tables-prefix=none

	php artisan identify:install --tables-prefix=identify

The former example will remove the prefix from all of the table names, so you will get `users`, `roles`, etc. The latter example will change the default table prefix of `auth` to `identify` so your table names will be `identify_users`, `identify_roles`, etc.

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
		// the user has an "admin" and/or "user" role
	}

	if (Auth::hasRole(['admin', 'user']))
	{
		// the user has an "admin" and/or "user" role (hasRole() is an alias of the is() method)
	}

	if (Auth::isAll(['admin', 'user']))
	{
		// the user has an "admin" and "user" role
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

**Checking whether a user has a particular permission:**

	if (Auth::can('manage-posts'))
	{
		// the user has a "manage-posts" permission
	}

	if (Auth::can(['manage-posts', 'manage-users']))
	{
		// the user has a "manage-posts" and/or "manage-users" permission
	}

	if (Auth::hasPermission(['manage-posts', 'manage-users']))
	{
		// the user has a "manage-posts" and/or "manage-users" permission (hasPermission() is an alias of the has() method)
	}

	if (Auth::hasPermissions(['manage-posts', 'manage-users']))
	{
		// the user has a "manage-posts" and "manage-users" permission
	}

> **Note:** Permissions can be hierarchical, so a "manage" permission may contain "manage-posts", "manage-users", etc. In this case, `Auth::can('manage-posts')` will be satisfied if the user has the parent "manage" permission. Users may have permissions directly applied to their user accounts or indirectly via roles. Roles may have a set of permissions associated with them that users will inherit.

**Adding or removing permissions:**

	$user = Auth::user();

	$user->addPermission('manage-posts'); // add "manage-posts" permission

	$user->addPermission(1); // add permission with ID of 1

	$user->removePermission('manage-posts'); // remove "manage-posts" permission

	$user->removePermission(1); // remove permission with ID of 1

	// adding or removing multiple permissions

	$user->addPermissions(['manage-posts', 'manage-users']);

	$user->removePermissions(['manage-posts', 'manage-users']);

> **Note:** These methods are necessary because there is an `auth_user_permissions_cached` table that is updated when permissions are updated to reduce the number of necessary permissions-related database queries.

**Authorize a specific role or roles:**

	// redirect to "home" URI if the user does not have one of the specified roles
	Auth::authorizeByRole(['admin', 'user'], 'home');

	// with a custom message (otherwise a default one is provided)
	Auth::authorizeByRole(['admin', 'user'], 'home', 'You are not authorized to access the requested page.');

**Authorize a specific permission or permissions:**

	// redirect to "home" URI if the user does not have one of the specified roles
	Auth::authorize(['manage-posts', 'manage-users'], 'home');

	// with a custom message (otherwise a default one is provided)
	Auth::authorize(['manage-posts', 'manage-users'], 'home', 'You are not authorized to access the requested page.');

**Automatically redirect to a URI with the unauthorized message:**

	// redirect to "home" URI if the user does not have one of the specified roles
	return Auth::unauthorized('home');

	// with a custom message (otherwise a default one is provided)
	return Auth::unauthorized('home', 'You are not authorized to access the requested page.');

The third argument is the name of the session variable. The default is 'messages' so if the user is redirected, `Session::get('messages')` will return an array like:

	['error' => 'You are not authorized to access the requested page.']

**Querying users based on a specific role or roles:**

	$users = User::onlyRoles('admin')->get(); // get users that have "admin" role

	$users = User::onlyRoles(['admin', 'mod'])->get(); // get users that have "admin" or "mod" role

	$users = User::exceptRoles('admin')->get(); // get users that do not have "admin" role

	$users = User::exceptRoles(['admin', 'mod'])->get(); // get users that do not have "admin" or "mod" role

> **Note:** The `exceptRoles()` scope will still return users that have another role that isn't in the array.

<a name="route-permissions"></a>
## Route Permissions

**Check whether a user has route access based on route permissions:**

	if (Auth::hasRouteAccess('pages.edit'))
	{
		// user has access to "pages.edit" route
	}

> **Note:** This and the hasAccess() require you to set up route permissions in `config/auth_routes.php`.

**Check whether a user has access to a URI based on route permissions:**

	if (Auth::hasAccess('pages/edit/home'))
	{
		// user has access to "pages/edit/home" URI (based on "config/auth_routes.php" route permissions mapping)
	}

To use hasRouteAccess() and hasAccess(), you may set up `config/auth_routes.php` to include the routes you would like to set permissions on:

	return [

		'admin.*'          => ['manage'],                                // user must have "manage" permission
		'admin.pages.*'    => ['manage-pages', 'demo'],                  // user must have "manage-pages" or "demo" permission
		'admin.forms.*'    => ['manage-pages', 'manage-forms', '[ALL]'], // user must have "manage-pages" and "manage-forms" permission
		'admin.forms.view' => ['view-forms'],                            // the most specifically defined route will always be checked

	];

<a name="accounts-emails"></a>
## Creating Accounts and Sending Emails

**Create a new user account:**

	Auth::createUser();

	// use custom input array
	Auth::createUser([
		'name'        => 'TestUser',
		'email'       => 'test@localhost',
		'password'    => 'password',
		'role_id'     => 2,
		'permissions' => ['manage-pages', 'manage-users'],
	]);

	// automatically activate user account
	Auth::createUser($input, true);

	// suppress confirmation email
	Auth::createUser($input, true, false);

**Create a new user account via the command line interface:**

	// use default password of "password"
	php artisan user:create username email@address.com

	// use alternate password
	php artisan user:create username email@address.com --password=anotherpassword

	// automatically activate user
	php artisan user:create username email@address.com --activate

	// automatically activate user and suppress confirmation email
	php artisan user:create username email@address.com --activate --suppress

**Send an email to a user with a specific view in `views/emails`:**

	Auth::sendEmail($user, 'confirmation');

	Auth::sendEmail($user, 'banned');

	Auth::sendEmail($user, 'deleted');

	Auth::sendEmail($user, 'password');

**Activate a user account by ID and activation token:**

	if (Auth::activate(1, 'wHHhONhavZps1J9p8Rs6WIXsTK30tFhl'))
	{
		// user ID #1 has been activated
	}