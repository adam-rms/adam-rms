<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Emailverificationcode
 * 
 * @property int $emailVerificationCodes_id
 * @property string $emailVerificationCodes_code
 * @property bool $emailVerificationCodes_used
 * @property Carbon $emailVerificationCodes_timestamp
 * @property int $emailVerificationCodes_valid
 * @property int $users_userid
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Emailverificationcode extends Model
{
	protected $table = 'emailverificationcodes';
	protected $primaryKey = 'emailVerificationCodes_id';
	public $timestamps = false;

	protected $casts = [
		'emailVerificationCodes_used' => 'bool',
		'emailVerificationCodes_valid' => 'int',
		'users_userid' => 'int'
	];

	protected $dates = [
		'emailVerificationCodes_timestamp'
	];

	protected $fillable = [
		'emailVerificationCodes_code',
		'emailVerificationCodes_used',
		'emailVerificationCodes_timestamp',
		'emailVerificationCodes_valid',
		'users_userid'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
