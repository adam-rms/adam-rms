<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Passwordresetcode
 * 
 * @property int $passwordResetCodes_id
 * @property string $passwordResetCodes_code
 * @property bool $passwordResetCodes_used
 * @property Carbon $passwordResetCodes_timestamp
 * @property int $passwordResetCodes_valid
 * @property int $users_userid
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Passwordresetcode extends Model
{
	protected $table = 'passwordresetcodes';
	protected $primaryKey = 'passwordResetCodes_id';
	public $timestamps = false;

	protected $casts = [
		'passwordResetCodes_used' => 'bool',
		'passwordResetCodes_valid' => 'int',
		'users_userid' => 'int'
	];

	protected $dates = [
		'passwordResetCodes_timestamp'
	];

	protected $fillable = [
		'passwordResetCodes_code',
		'passwordResetCodes_used',
		'passwordResetCodes_timestamp',
		'passwordResetCodes_valid',
		'users_userid'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
