<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Maintenancejobsmessage
 * 
 * @property int $maintenanceJobsMessages_id
 * @property int|null $maintenanceJobs_id
 * @property Carbon $maintenanceJobsMessages_timestamp
 * @property int $users_userid
 * @property bool $maintenanceJobsMessages_deleted
 * @property string|null $maintenanceJobsMessages_text
 * @property int|null $maintenanceJobsMessages_file
 * 
 * @property S3file|null $s3file
 * @property Maintenancejob|null $maintenancejob
 *
 * @package App\Models
 */
class Maintenancejobsmessage extends Model
{
	protected $table = 'maintenancejobsmessages';
	protected $primaryKey = 'maintenanceJobsMessages_id';
	public $timestamps = false;

	protected $casts = [
		'maintenanceJobs_id' => 'int',
		'users_userid' => 'int',
		'maintenanceJobsMessages_deleted' => 'bool',
		'maintenanceJobsMessages_file' => 'int'
	];

	protected $dates = [
		'maintenanceJobsMessages_timestamp'
	];

	protected $fillable = [
		'maintenanceJobs_id',
		'maintenanceJobsMessages_timestamp',
		'users_userid',
		'maintenanceJobsMessages_deleted',
		'maintenanceJobsMessages_text',
		'maintenanceJobsMessages_file'
	];

	public function s3file()
	{
		return $this->belongsTo(S3file::class, 'maintenanceJobsMessages_file');
	}

	public function maintenancejob()
	{
		return $this->belongsTo(Maintenancejob::class, 'maintenanceJobs_id');
	}
}
