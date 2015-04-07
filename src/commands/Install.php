<?php namespace Regulus\Identify\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Support\Facades\Config;

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
		$defaultTableName = "auth_users";
		if ($this->option('table') != $defaultTableName)
		{
			$config = str_replace($defaultTableName, $this->option('table'), file_get_contents('config/auth.php'));

			file_put_contents('config/auth.php', $config);

			Config::set('auth.table', $this->option('table'));
		} else {
			Config::set('auth.table', $defaultTableName);
		}

		// run database migrations
		$this->output->writeln('');
		$this->comment('Migrating DB tables...');
		$this->info($divider);

		$this->call('migrate', [
			'--env' => $this->option('env'),
		]);

		$this->call('migrate', [
			'--env'  => $this->option('env'),
			'--path' => 'vendor/regulus/identify/src/migrations',
		]);

		// seed database tables
		$this->output->writeln('');
		$this->comment('Seeding DB tables...');
		$this->info($divider);

		$this->call('db:seed', ['--class' => 'IdentifySeeder']);

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
				'table',
				't',
				InputOption::VALUE_OPTIONAL,
				'The name of the users table (from which the other table names are derived).',
				'auth_users',
			],
		];
	}

}