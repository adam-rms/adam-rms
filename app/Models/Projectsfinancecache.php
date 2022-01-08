<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Projectsfinancecache
 * 
 * @property int $projectsFinanceCache_id
 * @property int $projects_id
 * @property Carbon $projectsFinanceCache_timestamp
 * @property Carbon|null $projectsFinanceCache_timestampUpdated
 * @property int|null $projectsFinanceCache_equipmentSubTotal
 * @property int|null $projectsFinanceCache_equiptmentDiscounts
 * @property int|null $projectsFinanceCache_equiptmentTotal
 * @property int|null $projectsFinanceCache_salesTotal
 * @property int|null $projectsFinanceCache_staffTotal
 * @property int|null $projectsFinanceCache_externalHiresTotal
 * @property int|null $projectsFinanceCache_paymentsReceived
 * @property int|null $projectsFinanceCache_grandTotal
 * @property int|null $projectsFinanceCache_value
 * @property float|null $projectsFinanceCache_mass
 * 
 * @property Project $project
 *
 * @package App\Models
 */
class Projectsfinancecache extends Model
{
	protected $table = 'projectsfinancecache';
	protected $primaryKey = 'projectsFinanceCache_id';
	public $timestamps = false;

	protected $casts = [
		'projects_id' => 'int',
		'projectsFinanceCache_equipmentSubTotal' => 'int',
		'projectsFinanceCache_equiptmentDiscounts' => 'int',
		'projectsFinanceCache_equiptmentTotal' => 'int',
		'projectsFinanceCache_salesTotal' => 'int',
		'projectsFinanceCache_staffTotal' => 'int',
		'projectsFinanceCache_externalHiresTotal' => 'int',
		'projectsFinanceCache_paymentsReceived' => 'int',
		'projectsFinanceCache_grandTotal' => 'int',
		'projectsFinanceCache_value' => 'int',
		'projectsFinanceCache_mass' => 'float'
	];

	protected $dates = [
		'projectsFinanceCache_timestamp',
		'projectsFinanceCache_timestampUpdated'
	];

	protected $fillable = [
		'projects_id',
		'projectsFinanceCache_timestamp',
		'projectsFinanceCache_timestampUpdated',
		'projectsFinanceCache_equipmentSubTotal',
		'projectsFinanceCache_equiptmentDiscounts',
		'projectsFinanceCache_equiptmentTotal',
		'projectsFinanceCache_salesTotal',
		'projectsFinanceCache_staffTotal',
		'projectsFinanceCache_externalHiresTotal',
		'projectsFinanceCache_paymentsReceived',
		'projectsFinanceCache_grandTotal',
		'projectsFinanceCache_value',
		'projectsFinanceCache_mass'
	];

	public function project()
	{
		return $this->belongsTo(Project::class, 'projects_id');
	}
}
