<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Loginattempt
 * 
 * @property int $loginAttempts_id
 * @property Carbon $loginAttempts_timestamp
 * @property string $loginAttempts_textEntered
 * @property string|null $loginAttempts_ip
 * @property bool $loginAttempts_blocked
 * @property bool $loginAttempts_successful
 *
 * @package App\Models
 */
class Loginattempt extends Model
{
	protected $table = 'loginattempts';
	protected $primaryKey = 'loginAttempts_id';
	public $timestamps = false;

	protected $casts = [
		'loginAttempts_blocked' => 'bool',
		'loginAttempts_successful' => 'bool'
	];

	protected $dates = [
		'loginAttempts_timestamp'
	];

	protected $fillable = [
		'loginAttempts_timestamp',
		'loginAttempts_textEntered',
		'loginAttempts_ip',
		'loginAttempts_blocked',
		'loginAttempts_successful'
	];
}
