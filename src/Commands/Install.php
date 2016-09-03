<?php namespace Regulus\Identify\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Config;
use File;

class Install extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'identify:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Identify\'s install command.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$divider = '----------------------';

		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Installing Identify...');
		$this->info($divider);

		// publish config files and assets for Identify and its required packages
		$this->output->writeln('');
		$this->comment('Publishing configuration and assets...');
		$this->info($divider);

		$publishOptions = ['--env' => $this->option('env')];

		// if "identify" variable doesn't exist in auth, it hasn't been published yet so "vendor:publish" command should be forced
		if (!config('auth.identify'))
			$publishOptions['--force'] = true;

		$this->call('vendor:publish', $publishOptions);

		// adjust table name if a table name option is set
		$defaultTablesPrefix = "auth";
		$tablesPrefix        = $this->option('tables-prefix');
		if ($tablesPrefix != $defaultTablesPrefix)
		{
			if (in_array($tablesPrefix, ['none', 'null', 'false']))
				$tablesPrefix = "";

			$replacePrefix = "'tables_prefix' => '";

			$config = str_replace($replacePrefix.$defaultTablesPrefix, $replacePrefix.$tablesPrefix, file_get_contents('config/auth.php'));

			file_put_contents('config/auth.php', $config);

			Config::set('auth.tables_prefix', $tablesPrefix);
		} else {
			Config::set('auth.tables_prefix', $defaultTablesPrefix);
		}

		// run database migrations
		$this->output->writeln('');
		$this->comment('Migrating DB tables...');
		$this->info($divider);

		// remove Laravel's own users table migration
		$migrationFile = database_path('migrations/2014_10_12_000000_create_users_table.php');
		if (File::exists($migrationFile))
		{
			File::delete($migrationFile);

			$this->comment('Removed Laravel\'s own "create_users_table" migration.');
		}

		$this->call('migrate', [
			'--env' => $this->option('env'),
		]);

		// seed database tables
		$this->output->writeln('');
		$this->comment('Seeding DB tables...');
		$this->info($divider);

		$this->call('db:seed', ['--class' => 'IdentifySeeder']);

		// copy error views
		$this->output->writeln('');
		$this->comment('Copying error views...');
		$this->info($divider);

		$errorViewsDirectory = "resources/views/errors";
		if (!is_dir($errorViewsDirectory))
			mkdir($errorViewsDirectory);

		$errorViewsPartialsDirectory = $errorViewsDirectory.'/partials';
		if (!is_dir($errorViewsPartialsDirectory))
			mkdir($errorViewsPartialsDirectory);

		$errorViewsSourceDirectory = "vendor/regulus/identify/src/resources/views/errors";

		$errorViewFiles = [
			'401',
			'403',
			'404',
			'layout',
			'partials/dev_info',
		];
		foreach ($errorViewFiles as $errorViewFile)
		{
			copy($errorViewsSourceDirectory.'/'.$errorViewFile.'.blade.php', $errorViewsDirectory.'/'.$errorViewFile.'.blade.php');
		}

		$this->info('Error views copied.');

		// show installed text
		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Identify installed!');
		$this->info($divider);
		$this->output->writeln('');

		return;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	public function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return [
			[
				'tables-prefix',
				't',
				InputOption::VALUE_OPTIONAL,
				'The prefix for the users tables.',
				'auth',
			],
		];
	}

}