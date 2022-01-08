<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Assetsassignment
 * 
 * @property int $assetsAssignments_id
 * @property int $assets_id
 * @property int $projects_id
 * @property string|null $assetsAssignments_comment
 * @property int $assetsAssignments_customPrice
 * @property float $assetsAssignments_discount
 * @property Carbon|null $assetsAssignments_timestamp
 * @property bool $assetsAssignments_deleted
 * @property int|null $assetsAssignmentsStatus_id
 * @property int|null $assetsAssignments_linkedTo
 * 
 * @property Asset $asset
 * @property Assetsassignment|null $assetsassignment
 * @property Project $project
 * @property Collection|Assetsassignment[] $assetsassignments
 *
 * @package App\Models
 */
class Assetsassignment extends Model
{
	protected $table = 'assetsassignments';
	protected $primaryKey = 'assetsAssignments_id';
	public $timestamps = false;

	protected $casts = [
		'assets_id' => 'int',
		'projects_id' => 'int',
		'assetsAssignments_customPrice' => 'int',
		'assetsAssignments_discount' => 'float',
		'assetsAssignments_deleted' => 'bool',
		'assetsAssignmentsStatus_id' => 'int',
		'assetsAssignments_linkedTo' => 'int'
	];

	protected $dates = [
		'assetsAssignments_timestamp'
	];

	protected $fillable = [
		'assets_id',
		'projects_id',
		'assetsAssignments_comment',
		'assetsAssignments_customPrice',
		'assetsAssignments_discount',
		'assetsAssignments_timestamp',
		'assetsAssignments_deleted',
		'assetsAssignmentsStatus_id',
		'assetsAssignments_linkedTo'
	];

	public function asset()
	{
		return $this->belongsTo(Asset::class, 'assets_id');
	}

	public function assetsassignment()
	{
		return $this->belongsTo(Assetsassignment::class, 'assetsAssignments_linkedTo');
	}

	public function project()
	{
		return $this->belongsTo(Project::class, 'projects_id');
	}

	public function assetsassignments()
	{
		return $this->hasMany(Assetsassignment::class, 'assetsAssignments_linkedTo');
	}
}
