<?php namespace Regulus\Identify;

use Illuminate\Support\ServiceProvider;

class IdentifyServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'/config/auth.php'        => config_path('auth.php'),
			__DIR__.'/config/auth_routes.php' => config_path('auth_routes.php'),
			__DIR__.'/resources/lang'         => resource_path('lang/vendor/identify'),
			__DIR__.'/resources/views'        => resource_path('views/vendor/identify'),
		]);

		$this->publishes([
			__DIR__.'/database/migrations' => database_path('migrations'),
		], 'migrations');

		$this->loadTranslationsFrom(__DIR__.'/resources/lang', 'identify');

		$this->loadViewsFrom(__DIR__.'/resources/views', 'identify');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		\Auth::extend('session', function($app, $name, array $config)
		{
			$model = $app['config']['auth.providers.users.model'];

			$provider = new IdentifyUserProvider($app['hash'], $model);

			return new Identify($name, $provider, $this->app['session.store'], $this->app['request']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['Regulus\Identify\Identify'];
	}

}