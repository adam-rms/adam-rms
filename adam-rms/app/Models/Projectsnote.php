<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Projectsnote
 * 
 * @property int $projectsNotes_id
 * @property string $projectsNotes_title
 * @property string|null $projectsNotes_text
 * @property int $projectsNotes_userid
 * @property int $projects_id
 * @property bool $projectsNotes_deleted
 * 
 * @property Project $project
 * @property User $user
 *
 * @package App\Models
 */
class Projectsnote extends Model
{
	protected $table = 'projectsnotes';
	protected $primaryKey = 'projectsNotes_id';
	public $timestamps = false;

	protected $casts = [
		'projectsNotes_userid' => 'int',
		'projects_id' => 'int',
		'projectsNotes_deleted' => 'bool'
	];

	protected $fillable = [
		'projectsNotes_title',
		'projectsNotes_text',
		'projectsNotes_userid',
		'projects_id',
		'projectsNotes_deleted'
	];

	public function project()
	{
		return $this->belongsTo(Project::class, 'projects_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'projectsNotes_userid');
	}
}
