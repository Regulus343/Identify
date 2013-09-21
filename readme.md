Identify
========

**A composer package that adds roles and many other features to Laravel 4's basic authentication/authorization.**

- [Composer Package Installation](#composer-package-installation)
- [Installation: Command Line](#command-line-installation)
- [Installation: Manual](#manual-installation)
- [Migrations and Database Seeding](#migrations-seeding)
- [Basic Usage](#basic-usage)

<a name="composer-package-installation"></a>
## Composer Package Installation

To install Identify, make sure "regulus/identify" has been added to Laravel 4's `composer.json` file.

	"require": {
		"regulus/identify": "dev-master"
	},

Then run `php composer.phar update` from the command line. Composer will install the Identify package.

<a name="command-line-installation"></a>
## Command Line Installation

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Identify's alias in `app/config/app.php`, set 'model' to `Regulus\Identify\User` in `app/config/auth.php`, and run the install command. Add this to the `providers` array:

	'Regulus\Identify\IdentifyServiceProvider',

And add this to the `aliases` array:

	'Auth' => 'Regulus\Identify\Identify',

You may use 'Identify', or another alias, but 'Auth' is recommended for the sake of simplicity.

Next, change the `model` variable in `app/config/auth.php` to `Regulus\Identify\User`.

**Run the install command:**

	php artisan identify:install

Identify should now be installed.

You should now have 4 users, 'Admin', 'TestUser', 'TestUser2', and 'TestUser3'. All of the passwords are 'password' and the usernames are case insensitive, so you may simply type 'admin' and 'password' to log in. The 3 initial roles are 'Administrator', 'Moderator', and 'Member'. 'Admin' has the 'Administrator' role, 'TestUser' has the 'Moderator' role, the final 2 users have the 'Member' role.

You may now skip ahead to the [Basic Usage](#basic-usage) section.

<a name="manual-installation"></a>
## Manual Installation

**Publishing config file:**

If you wish to customize the configuration of Identify, you will need to publish the config file. Run this from the command line:

	php artisan config:publish regulus/identify

You will now be able to edit the config file in `app/config/packages/regulus/identify`.

**Run the migrations and seed the database:**

The default table prefix is 'auth_'. If you would like to remove it or use a different table prefix, you may do so in `config.php`. To run Identify's migrations run the following from the command line:

	php artisan migrate --package=regulus/identify

This will add the 'auth_users', 'auth_roles', and 'auth_user_roles' table. To start with 4 initial users, you may seed the database by adding the following to the `run()` method in `database/seeds/DatabaseSeeder.php`:

	$this->call('UsersTableSeeder');
	$this->command->info('Users table seeded.');

	$this->call('RolesTableSeeder');
	$this->command->info('Roles table seeded.');

	$this->call('UserRolesTableSeeder');
	$this->command->info('User Roles table seeded.');

...And then running `php artisan db:seed` from the command line. You should now have 4 users, 'Admin', 'TestUser', 'TestUser2', and 'TestUser3'. All of the passwords are 'password' and the usernames are case insensitive, so you may simply type 'admin' and 'password' to log in. The 3 initial roles are 'Administrator', 'Moderator', and 'Member'. 'Admin' has the 'Administrator' role, 'TestUser' has the 'Moderator' role, the final 2 users have the 'Member' role.

**Register service provider and set up alias:**

Now, all you have to do is register the service provider, set up Identify's alias in `app/config/app.php`, and set 'model' to `Regulus\Identify\User` in `app/config/auth.php`. Add this to the `providers` array:

	'Regulus\Identify\IdentifyServiceProvider',

And add this to the `aliases` array:

	'Auth' => 'Regulus\Identify\Identify',

You may use 'Identify', or another alias, but 'Auth' is recommended for the sake of simplicity.

Lastly, change the `model` variable in `app/config/auth.php` to `Regulus\Identify\User`.

<a name="basic-usage"></a>
## Basic Usage

**Checking whether a user is logged in:**

	if (Auth::check()) {
		//the user is logged in
	}

**Checking whether a user has a particular role:**

	if (Auth::is('admin')) {
		//the user has an "admin" role
	}

	if (Auth::is(array('admin', 'user'))) {
		//the user has an "admin" role and/or a "user" role
	}

**Checking whether a user has is not particular role:**

	if (Auth::isNot('admin')) {
		//the user lacks an "admin" role
	}

	if (Auth::isNot(array('admin', 'user'))) {
		//the user lacks the "admin" and "user" roles
	}

**Authorize a specific role or roles:**

	//redirect to "home" URI if the user does not have one of the specified roles
	Auth::authorize(array('admin', 'user'), 'home');

	//with a custom message (otherwise a default one is provided)
	Auth::authorize(array('admin', 'user'), 'home', 'You are not authorized to access the requested page.');

**Automatically redirect to a URI with the unauthorized message:**

	//redirect to "home" URI if the user does not have one of the specified roles
	return Auth::unauthorized('home');

	//with a custom message (otherwise a default one is provided)
	Auth::authorize('home', 'You are not authorized to access the requested page.');

The third argument is the name of the session variable. The default is 'messages' so if the user is redirected, `Session::get('messages')` will return an array like:

	array('error' => 'You are not authorized to access the requested page.')

**Create a new user account:**

	Auth::createAccount();

Create account will use `'dataSetup' => 'create'` in `config.php` to add the account data.

**Update a user account:**

	//the default data setup is 'standard' in the 'dataSetup' config array
	$user->updateAccount();

	//you may update one or more data types by specifying them
	$user->updateAccount('password');
	$user->updateAccount('ban');
	$user->updateAccount('delete');

	$user->updateAccount(array('standard', 'password'));

**Send an email to a user with a specific view in `views/emails`:**

	Auth::sendEmail($user, 'signup_confirmation');

	Auth::sendEmail($user, 'banned');

	Auth::sendEmail($user, 'deleted');

	Auth::sendEmail($user, 'reset_password');

**Activate a user account by ID and activation code:**

	if (Auth::activate(1, '47381703b56f583133011c8899ffa1bd')) {
		//user ID #1 has been activated
	}

**Set the reset password code and send the user an email with password reset instructions:**

	$user->resetPasswordCode();