<?php namespace Regulus\Identify;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;

class StateItem extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_states';

	/**
	 * The attributes that cannot be updated.
	 *
	 * @var array
	 */
	protected $guarded = array('id');

	/**
	 * The constructor which adds the table prefix from the config settings.
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$this->table = Config::get('identify::tablePrefix').$this->table;
	}

	/**
	 * The user that the state belongs to.
	 *
	 * @return object
	 */
	public function user()
	{
		return $this->belongsTo('Regulus\Identify\User');
	}

}