<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Projectsvacantrolesapplication
 * 
 * @property int $projectsVacantRolesApplications_id
 * @property int $projectsVacantRoles_id
 * @property int $users_userid
 * @property string|null $projectsVacantRolesApplications_files
 * @property string|null $projectsVacantRolesApplications_phone
 * @property string|null $projectsVacantRolesApplications_applicantComment
 * @property bool $projectsVacantRolesApplications_deleted
 * @property bool $projectsVacantRolesApplications_withdrawn
 * @property Carbon $projectsVacantRolesApplications_submitted
 * @property array|null $projectsVacantRolesApplications_questionAnswers
 * @property bool $projectsVacantRolesApplications_status
 * 
 * @property Projectsvacantrole $projectsvacantrole
 * @property User $user
 *
 * @package App\Models
 */
class Projectsvacantrolesapplication extends Model
{
	protected $table = 'projectsvacantrolesapplications';
	protected $primaryKey = 'projectsVacantRolesApplications_id';
	public $timestamps = false;

	protected $casts = [
		'projectsVacantRoles_id' => 'int',
		'users_userid' => 'int',
		'projectsVacantRolesApplications_deleted' => 'bool',
		'projectsVacantRolesApplications_withdrawn' => 'bool',
		'projectsVacantRolesApplications_questionAnswers' => 'json',
		'projectsVacantRolesApplications_status' => 'bool'
	];

	protected $dates = [
		'projectsVacantRolesApplications_submitted'
	];

	protected $fillable = [
		'projectsVacantRoles_id',
		'users_userid',
		'projectsVacantRolesApplications_files',
		'projectsVacantRolesApplications_phone',
		'projectsVacantRolesApplications_applicantComment',
		'projectsVacantRolesApplications_deleted',
		'projectsVacantRolesApplications_withdrawn',
		'projectsVacantRolesApplications_submitted',
		'projectsVacantRolesApplications_questionAnswers',
		'projectsVacantRolesApplications_status'
	];

	public function projectsvacantrole()
	{
		return $this->belongsTo(Projectsvacantrole::class, 'projectsVacantRoles_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
