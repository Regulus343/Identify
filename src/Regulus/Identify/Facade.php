<?php namespace Regulus\Identify;

class Facade extends \Illuminate\Support\Facades\Facade {

	protected static function getFacadeAccessor() { return 'identify'; }

}