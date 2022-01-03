<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Project
 * 
 * @property int $projects_id
 * @property string $projects_name
 * @property int $instances_id
 * @property int $projects_manager
 * @property string|null $projects_description
 * @property Carbon $projects_created
 * @property int|null $clients_id
 * @property bool $projects_deleted
 * @property bool $projects_archived
 * @property Carbon|null $projects_dates_use_start
 * @property Carbon|null $projects_dates_use_end
 * @property Carbon|null $projects_dates_deliver_start
 * @property Carbon|null $projects_dates_deliver_end
 * @property int $projects_status
 * @property int|null $locations_id
 * @property string|null $projects_invoiceNotes
 * @property float $projects_defaultDiscount
 * @property int $projectsTypes_id
 * @property int|null $projects_parent_project_id
 * 
 * @property Client|null $client
 * @property Project|null $project
 * @property Instance $instance
 * @property Location|null $location
 * @property User $user
 * @property Collection|Assetsassignment[] $assetsassignments
 * @property Collection|Crewassignment[] $crewassignments
 * @property Collection|Payment[] $payments
 * @property Collection|Project[] $projects
 * @property Collection|Projectsfinancecache[] $projectsfinancecaches
 * @property Collection|Projectsnote[] $projectsnotes
 * @property Collection|Projectsvacantrole[] $projectsvacantroles
 *
 * @package App\Models
 */
class Project extends Model
{
	protected $table = 'projects';
	protected $primaryKey = 'projects_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'projects_manager' => 'int',
		'clients_id' => 'int',
		'projects_deleted' => 'bool',
		'projects_archived' => 'bool',
		'projects_status' => 'int',
		'locations_id' => 'int',
		'projects_defaultDiscount' => 'float',
		'projectsTypes_id' => 'int',
		'projects_parent_project_id' => 'int'
	];

	protected $dates = [
		'projects_created',
		'projects_dates_use_start',
		'projects_dates_use_end',
		'projects_dates_deliver_start',
		'projects_dates_deliver_end'
	];

	protected $fillable = [
		'projects_name',
		'instances_id',
		'projects_manager',
		'projects_description',
		'projects_created',
		'clients_id',
		'projects_deleted',
		'projects_archived',
		'projects_dates_use_start',
		'projects_dates_use_end',
		'projects_dates_deliver_start',
		'projects_dates_deliver_end',
		'projects_status',
		'locations_id',
		'projects_invoiceNotes',
		'projects_defaultDiscount',
		'projectsTypes_id',
		'projects_parent_project_id'
	];

	public function client()
	{
		return $this->belongsTo(Client::class, 'clients_id');
	}

	public function project()
	{
		return $this->belongsTo(Project::class, 'projects_parent_project_id');
	}

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function location()
	{
		return $this->belongsTo(Location::class, 'locations_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'projects_manager');
	}

	public function assetsassignments()
	{
		return $this->hasMany(Assetsassignment::class, 'projects_id');
	}

	public function crewassignments()
	{
		return $this->hasMany(Crewassignment::class, 'projects_id');
	}

	public function payments()
	{
		return $this->hasMany(Payment::class, 'projects_id');
	}

	public function projects()
	{
		return $this->hasMany(Project::class, 'projects_parent_project_id');
	}

	public function projectsfinancecaches()
	{
		return $this->hasMany(Projectsfinancecache::class, 'projects_id');
	}

	public function projectsnotes()
	{
		return $this->hasMany(Projectsnote::class, 'projects_id');
	}

	public function projectsvacantroles()
	{
		return $this->hasMany(Projectsvacantrole::class, 'projects_id');
	}
}
