<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Crewassignment
 * 
 * @property int $crewAssignments_id
 * @property int|null $users_userid
 * @property int $projects_id
 * @property string|null $crewAssignments_personName
 * @property string $crewAssignments_role
 * @property string|null $crewAssignments_comment
 * @property bool|null $crewAssignments_deleted
 * @property int|null $crewAssignments_rank
 * 
 * @property Project $project
 * @property User|null $user
 *
 * @package App\Models
 */
class Crewassignment extends Model
{
	protected $table = 'crewassignments';
	protected $primaryKey = 'crewAssignments_id';
	public $timestamps = false;

	protected $casts = [
		'users_userid' => 'int',
		'projects_id' => 'int',
		'crewAssignments_deleted' => 'bool',
		'crewAssignments_rank' => 'int'
	];

	protected $fillable = [
		'users_userid',
		'projects_id',
		'crewAssignments_personName',
		'crewAssignments_role',
		'crewAssignments_comment',
		'crewAssignments_deleted',
		'crewAssignments_rank'
	];

	public function project()
	{
		return $this->belongsTo(Project::class, 'projects_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
