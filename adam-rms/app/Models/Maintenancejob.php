<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Maintenancejob
 * 
 * @property int $maintenanceJobs_id
 * @property string $maintenanceJobs_assets
 * @property Carbon $maintenanceJobs_timestamp_added
 * @property Carbon|null $maintenanceJobs_timestamp_due
 * @property string|null $maintenanceJobs_user_tagged
 * @property int $maintenanceJobs_user_creator
 * @property int|null $maintenanceJobs_user_assignedTo
 * @property string|null $maintenanceJobs_title
 * @property string|null $maintenanceJobs_faultDescription
 * @property int $maintenanceJobs_priority
 * @property int $instances_id
 * @property bool $maintenanceJobs_deleted
 * @property int|null $maintenanceJobsStatuses_id
 * @property bool $maintenanceJobs_flagAssets
 * @property bool $maintenanceJobs_blockAssets
 * 
 * @property User $user
 * @property Collection|Maintenancejobsmessage[] $maintenancejobsmessages
 *
 * @package App\Models
 */
class Maintenancejob extends Model
{
	protected $table = 'maintenancejobs';
	protected $primaryKey = 'maintenanceJobs_id';
	public $timestamps = false;

	protected $casts = [
		'maintenanceJobs_user_creator' => 'int',
		'maintenanceJobs_user_assignedTo' => 'int',
		'maintenanceJobs_priority' => 'int',
		'instances_id' => 'int',
		'maintenanceJobs_deleted' => 'bool',
		'maintenanceJobsStatuses_id' => 'int',
		'maintenanceJobs_flagAssets' => 'bool',
		'maintenanceJobs_blockAssets' => 'bool'
	];

	protected $dates = [
		'maintenanceJobs_timestamp_added',
		'maintenanceJobs_timestamp_due'
	];

	protected $fillable = [
		'maintenanceJobs_assets',
		'maintenanceJobs_timestamp_added',
		'maintenanceJobs_timestamp_due',
		'maintenanceJobs_user_tagged',
		'maintenanceJobs_user_creator',
		'maintenanceJobs_user_assignedTo',
		'maintenanceJobs_title',
		'maintenanceJobs_faultDescription',
		'maintenanceJobs_priority',
		'instances_id',
		'maintenanceJobs_deleted',
		'maintenanceJobsStatuses_id',
		'maintenanceJobs_flagAssets',
		'maintenanceJobs_blockAssets'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'maintenanceJobs_user_creator');
	}

	public function maintenancejobsmessages()
	{
		return $this->hasMany(Maintenancejobsmessage::class, 'maintenanceJobs_id');
	}
}
