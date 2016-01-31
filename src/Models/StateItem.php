<?php namespace Regulus\Identify\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;

class StateItem extends Model {

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
	protected $guarded = ['id'];

	/**
	 * The constructor which adds the table prefix from the config settings.
	 *
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$this->table = Auth::getTableName($this->table);
	}

	/**
	 * The user that the state belongs to.
	 *
	 * @return User
	 */
	public function user()
	{
		return $this->belongsTo(config('auth.model'));
	}

}