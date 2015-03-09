<?php namespace Regulus\Identify\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Regulus\Identify\Facade as Auth;

use Illuminate\Support\Facades\Validator;

class CreateUser extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'user:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a user.';

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
		$input = [
			'name'     => trim($this->argument('name')),
			'email'    => trim($this->argument('email')),
			'password' => $this->option('password'),
		];

		if (is_null($input['name']))
		{
			$this->error('You must set a username as the first argument.');
			return;
		}

		if (is_null($input['email']))
		{
			$this->error('You must set an email address as the first argument.');
			return;
		}

		$val = Validator::make($input, [
			'name'  => ['required', 'unique:'.Auth::getTableName('users').',name'],
			'email' => ['required', 'email', 'unique:'.Auth::getTableName('users').',email'],
		]);

		if ($val->fails())
		{
			$errors = array_values($val->errors()->all());

			$this->error(implode("\n", $errors));
			return;
		}

		Auth::createUser($input, $this->option('activate'), !$this->option('suppress'));

		$this->comment('Created a user.');
		$this->output->writeln(' Username:      <info>'.$input['name'].'</info>');
		$this->output->writeln(' Email Address: <info>'.$input['email'].'</info>');
		$this->output->writeln(' Password:      <info>'.$input['password'].'</info>');
		$this->output->writeln(' Activated:     <info>'.($this->option('activate') ? 'Yes' : 'No').'</info>');
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
		return [
			[
				'name',
				InputOption::VALUE_REQUIRED,
				'The username of the user.',
			],
			[
				'email',
				InputOption::VALUE_REQUIRED,
				'The email address of the user.',
			],
		];
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
				'password',
				'p',
				InputOption::VALUE_OPTIONAL,
				'The password for the user.',
				'password',
			],
			[
				'activate',
				'a',
				InputOption::VALUE_OPTIONAL,
				'Whether to automatically activate the user.',
				false,
			],
			[
				'suppress',
				's',
				InputOption::VALUE_OPTIONAL,
				'Whether to suppress the confirmation email.',
				false,
			],
		];
	}

}