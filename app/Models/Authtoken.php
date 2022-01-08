<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Authtoken
 * 
 * @property int $authTokens_id
 * @property string $authTokens_token
 * @property Carbon $authTokens_created
 * @property string|null $authTokens_ipAddress
 * @property int $users_userid
 * @property bool $authTokens_valid
 * @property int|null $authTokens_adminId
 * @property string $authTokens_deviceType
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class Authtoken extends Model
{
	protected $table = 'authtokens';
	protected $primaryKey = 'authTokens_id';
	public $timestamps = false;

	protected $casts = [
		'users_userid' => 'int',
		'authTokens_valid' => 'bool',
		'authTokens_adminId' => 'int'
	];

	protected $dates = [
		'authTokens_created'
	];

	protected $hidden = [
		'authTokens_token'
	];

	protected $fillable = [
		'authTokens_token',
		'authTokens_created',
		'authTokens_ipAddress',
		'users_userid',
		'authTokens_valid',
		'authTokens_adminId',
		'authTokens_deviceType'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'authTokens_adminId');
	}
}
