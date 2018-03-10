<?php namespace Regulus\Identify\Models;

use Illuminate\Database\Eloquent\Model;

use Auth;
use Hash;

class ApiToken extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'api_tokens';

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

	/**
	 * Filter out expired API tokens.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOnlyActive($query)
	{
		return $query->where('expired_at', '>', date('Y-m-d H:i:s'));
	}

	/**
	 * Filter out active API tokens.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOnlyExpired($query)
	{
		return $query->where('expired_at', '<=', date('Y-m-d H:i:s'));
	}

	/**
	 * Filter out expired API tokens.
	 *
	 * @param  boolean  $token
	 * @return boolean
	 */
	public function check($token)
	{
		return Hash::check($token, $this->token);
	}

	/**
	 * Reset the API token.
	 *
	 * @return string
	 */
	public function reset()
	{
		$token = Auth::makeNewApiToken();

		$this->update(['token' => Hash::make($token)]);

		return $token;
	}

}