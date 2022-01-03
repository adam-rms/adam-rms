<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Modulesstep
 * 
 * @property int $modulesSteps_id
 * @property int $modules_id
 * @property bool $modulesSteps_deleted
 * @property bool $modulesSteps_show
 * @property string $modulesSteps_name
 * @property bool $modulesSteps_type
 * @property string|null $modulesSteps_content
 * @property int|null $modulesSteps_completionTime
 * @property string|null $modulesSteps_internalNotes
 * @property int $modulesSteps_order
 * @property bool $modulesSteps_locked
 * 
 * @property Module $module
 * @property Collection|Usermodule[] $usermodules
 *
 * @package App\Models
 */
class Modulesstep extends Model
{
	protected $table = 'modulessteps';
	protected $primaryKey = 'modulesSteps_id';
	public $timestamps = false;

	protected $casts = [
		'modules_id' => 'int',
		'modulesSteps_deleted' => 'bool',
		'modulesSteps_show' => 'bool',
		'modulesSteps_type' => 'bool',
		'modulesSteps_completionTime' => 'int',
		'modulesSteps_order' => 'int',
		'modulesSteps_locked' => 'bool'
	];

	protected $fillable = [
		'modules_id',
		'modulesSteps_deleted',
		'modulesSteps_show',
		'modulesSteps_name',
		'modulesSteps_type',
		'modulesSteps_content',
		'modulesSteps_completionTime',
		'modulesSteps_internalNotes',
		'modulesSteps_order',
		'modulesSteps_locked'
	];

	public function module()
	{
		return $this->belongsTo(Module::class, 'modules_id');
	}

	public function usermodules()
	{
		return $this->hasMany(Usermodule::class, 'userModules_currentStep');
	}
}
