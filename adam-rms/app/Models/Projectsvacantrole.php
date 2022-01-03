<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Projectsvacantrole
 * 
 * @property int $projectsVacantRoles_id
 * @property int $projects_id
 * @property string $projectsVacantRoles_name
 * @property string|null $projectsVacantRoles_description
 * @property string|null $projectsVacantRoles_personSpecification
 * @property bool $projectsVacantRoles_deleted
 * @property bool $projectsVacantRoles_open
 * @property bool $projectsVacantRoles_showPublic
 * @property Carbon $projectsVacantRoles_added
 * @property Carbon|null $projectsVacantRoles_deadline
 * @property bool $projectsVacantRoles_firstComeFirstServed
 * @property bool $projectsVacantRoles_fileUploads
 * @property int $projectsVacantRoles_slots
 * @property int $projectsVacantRoles_slotsFilled
 * @property array|null $projectsVacantRoles_questions
 * @property bool $projectsVacantRoles_collectPhone
 * @property bool $projectsVacantRoles_privateToPM
 * 
 * @property Project $project
 * @property Collection|Projectsvacantrolesapplication[] $projectsvacantrolesapplications
 *
 * @package App\Models
 */
class Projectsvacantrole extends Model
{
	protected $table = 'projectsvacantroles';
	protected $primaryKey = 'projectsVacantRoles_id';
	public $timestamps = false;

	protected $casts = [
		'projects_id' => 'int',
		'projectsVacantRoles_deleted' => 'bool',
		'projectsVacantRoles_open' => 'bool',
		'projectsVacantRoles_showPublic' => 'bool',
		'projectsVacantRoles_firstComeFirstServed' => 'bool',
		'projectsVacantRoles_fileUploads' => 'bool',
		'projectsVacantRoles_slots' => 'int',
		'projectsVacantRoles_slotsFilled' => 'int',
		'projectsVacantRoles_questions' => 'json',
		'projectsVacantRoles_collectPhone' => 'bool',
		'projectsVacantRoles_privateToPM' => 'bool'
	];

	protected $dates = [
		'projectsVacantRoles_added',
		'projectsVacantRoles_deadline'
	];

	protected $fillable = [
		'projects_id',
		'projectsVacantRoles_name',
		'projectsVacantRoles_description',
		'projectsVacantRoles_personSpecification',
		'projectsVacantRoles_deleted',
		'projectsVacantRoles_open',
		'projectsVacantRoles_showPublic',
		'projectsVacantRoles_added',
		'projectsVacantRoles_deadline',
		'projectsVacantRoles_firstComeFirstServed',
		'projectsVacantRoles_fileUploads',
		'projectsVacantRoles_slots',
		'projectsVacantRoles_slotsFilled',
		'projectsVacantRoles_questions',
		'projectsVacantRoles_collectPhone',
		'projectsVacantRoles_privateToPM'
	];

	public function project()
	{
		return $this->belongsTo(Project::class, 'projects_id');
	}

	public function projectsvacantrolesapplications()
	{
		return $this->hasMany(Projectsvacantrolesapplication::class, 'projectsVacantRoles_id');
	}
}
