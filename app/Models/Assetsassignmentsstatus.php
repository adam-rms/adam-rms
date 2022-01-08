<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Assetsassignmentsstatus
 * 
 * @property int $assetsAssignmentsStatus_id
 * @property int $instances_id
 * @property string $assetsAssignmentsStatus_name
 * @property int|null $assetsAssignmentsStatus_order
 * @property int $assetsAssignmentsStatus_deleted
 * 
 * @property Instance $instance
 *
 * @package App\Models
 */
class Assetsassignmentsstatus extends Model
{
	protected $table = 'assetsassignmentsstatus';
	protected $primaryKey = 'assetsAssignmentsStatus_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'assetsAssignmentsStatus_order' => 'int',
		'assetsAssignmentsStatus_deleted' => 'int'
	];

	protected $fillable = [
		'instances_id',
		'assetsAssignmentsStatus_name',
		'assetsAssignmentsStatus_order',
		'assetsAssignmentsStatus_deleted'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}
}
