<?php namespace Regulus\Identify\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Support\Facades\Config;

class InstallCommand extends Command {

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
		$this->info('----------------------');
		$this->comment('Installing Identify...');
		$this->info('----------------------');
		$this->output->writeln('');

		$package = "regulus/identify";

		//run database migrations
		$this->comment('Migrating DB tables...');
		$this->info($divider);

		$this->output->writeln('<info>Migrating DB tables:</info> '.$package);
		$this->call('migrate', array('--env' => $this->option('env'), '--package' => $package));

		$this->output->writeln('');

		//seed database tables
		$this->comment('Seeding DB tables...');
		$this->info($divider);

		$seedTables = array(
			'Users',
			'Roles',
			'UserRoles',
		);
		foreach ($seedTables as $seedTable) {
			$this->output->writeln('<info>Seeding DB table:</info> '.$seedTable);
			$this->call('db:seed', array('--class' => $seedTable.'TableSeeder'));
		}

		$this->output->writeln('');

		//publish config files for Identify and its required packages
		$this->comment('Publishing configuration...');
		$this->info($divider);

		$this->call('config:publish', array('--env' => $this->option('env'), 'package' => $package, '--path' => 'vendor/'.$package.'/src/config'));

		$this->output->writeln('');
		$this->info($divider);
		$this->comment('Identify installed!');
		$this->info($divider);
		$this->output->writeln('');
	}

}