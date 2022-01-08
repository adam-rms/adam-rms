<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Maintenancejobsstatus
 * 
 * @property int $maintenanceJobsStatuses_id
 * @property int|null $instances_id
 * @property string $maintenanceJobsStatuses_name
 * @property bool $maintenanceJobsStatuses_order
 * @property bool $maintenanceJobsStatuses_deleted
 * @property bool $maintenanceJobsStatuses_showJobInMainList
 * 
 * @property Instance|null $instance
 *
 * @package App\Models
 */
class Maintenancejobsstatus extends Model
{
	protected $table = 'maintenancejobsstatuses';
	protected $primaryKey = 'maintenanceJobsStatuses_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'maintenanceJobsStatuses_order' => 'bool',
		'maintenanceJobsStatuses_deleted' => 'bool',
		'maintenanceJobsStatuses_showJobInMainList' => 'bool'
	];

	protected $fillable = [
		'instances_id',
		'maintenanceJobsStatuses_name',
		'maintenanceJobsStatuses_order',
		'maintenanceJobsStatuses_deleted',
		'maintenanceJobsStatuses_showJobInMainList'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}
}
