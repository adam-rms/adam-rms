<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Auditlog
 * 
 * @property int $auditLog_id
 * @property string|null $auditLog_actionType
 * @property string|null $auditLog_actionTable
 * @property string|null $auditLog_actionData
 * @property Carbon $auditLog_timestamp
 * @property int|null $users_userid
 * @property int|null $auditLog_actionUserid
 * @property int|null $projects_id
 * @property bool $auditLog_deleted
 * @property int|null $auditLog_targetID
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class Auditlog extends Model
{
	protected $table = 'auditlog';
	protected $primaryKey = 'auditLog_id';
	public $timestamps = false;

	protected $casts = [
		'users_userid' => 'int',
		'auditLog_actionUserid' => 'int',
		'projects_id' => 'int',
		'auditLog_deleted' => 'bool',
		'auditLog_targetID' => 'int'
	];

	protected $dates = [
		'auditLog_timestamp'
	];

	protected $fillable = [
		'auditLog_actionType',
		'auditLog_actionTable',
		'auditLog_actionData',
		'auditLog_timestamp',
		'users_userid',
		'auditLog_actionUserid',
		'projects_id',
		'auditLog_deleted',
		'auditLog_targetID'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'auditLog_actionUserid');
	}
}
