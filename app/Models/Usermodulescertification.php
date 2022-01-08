<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Usermodulescertification
 * 
 * @property int $userModulesCertifications_id
 * @property int $modules_id
 * @property int $users_userid
 * @property bool $userModulesCertifications_revoked
 * @property int $userModulesCertifications_approvedBy
 * @property string|null $userModulesCertifications_approvedComment
 * @property Carbon $userModulesCertifications_timestamp
 * 
 * @property Module $module
 * @property User $user
 *
 * @package App\Models
 */
class Usermodulescertification extends Model
{
	protected $table = 'usermodulescertifications';
	protected $primaryKey = 'userModulesCertifications_id';
	public $timestamps = false;

	protected $casts = [
		'modules_id' => 'int',
		'users_userid' => 'int',
		'userModulesCertifications_revoked' => 'bool',
		'userModulesCertifications_approvedBy' => 'int'
	];

	protected $dates = [
		'userModulesCertifications_timestamp'
	];

	protected $fillable = [
		'modules_id',
		'users_userid',
		'userModulesCertifications_revoked',
		'userModulesCertifications_approvedBy',
		'userModulesCertifications_approvedComment',
		'userModulesCertifications_timestamp'
	];

	public function module()
	{
		return $this->belongsTo(Module::class, 'modules_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'userModulesCertifications_approvedBy');
	}
}
