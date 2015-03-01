<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class IdentifySeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$seeds = ['Users', 'Roles', 'Permissions'];

		foreach ($seeds as $seed)
		{
			$this->call($seed.'TableSeeder');
		}
	}

}
