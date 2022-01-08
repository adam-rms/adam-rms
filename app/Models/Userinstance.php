<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Userinstance
 * 
 * @property int $userInstances_id
 * @property int $users_userid
 * @property int $instancePositions_id
 * @property string|null $userInstances_extraPermissions
 * @property string|null $userInstances_label
 * @property bool $userInstances_deleted
 * @property int|null $signupCodes_id
 * @property Carbon|null $userInstances_archived
 * 
 * @property Instanceposition $instanceposition
 * @property Signupcode|null $signupcode
 * @property User $user
 *
 * @package App\Models
 */
class Userinstance extends Model
{
	protected $table = 'userinstances';
	protected $primaryKey = 'userInstances_id';
	public $timestamps = false;

	protected $casts = [
		'users_userid' => 'int',
		'instancePositions_id' => 'int',
		'userInstances_deleted' => 'bool',
		'signupCodes_id' => 'int'
	];

	protected $dates = [
		'userInstances_archived'
	];

	protected $fillable = [
		'users_userid',
		'instancePositions_id',
		'userInstances_extraPermissions',
		'userInstances_label',
		'userInstances_deleted',
		'signupCodes_id',
		'userInstances_archived'
	];

	public function instanceposition()
	{
		return $this->belongsTo(Instanceposition::class, 'instancePositions_id');
	}

	public function signupcode()
	{
		return $this->belongsTo(Signupcode::class, 'signupCodes_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
