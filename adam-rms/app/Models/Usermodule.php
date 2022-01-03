<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Usermodule
 * 
 * @property int $userModules_id
 * @property int $modules_id
 * @property int $users_userid
 * @property string|null $userModules_stepsCompleted
 * @property int|null $userModules_currentStep
 * @property Carbon $userModules_started
 * @property Carbon $userModules_updated
 * 
 * @property Module $module
 * @property Modulesstep|null $modulesstep
 * @property User $user
 *
 * @package App\Models
 */
class Usermodule extends Model
{
	protected $table = 'usermodules';
	protected $primaryKey = 'userModules_id';
	public $timestamps = false;

	protected $casts = [
		'modules_id' => 'int',
		'users_userid' => 'int',
		'userModules_currentStep' => 'int'
	];

	protected $dates = [
		'userModules_started',
		'userModules_updated'
	];

	protected $fillable = [
		'modules_id',
		'users_userid',
		'userModules_stepsCompleted',
		'userModules_currentStep',
		'userModules_started',
		'userModules_updated'
	];

	public function module()
	{
		return $this->belongsTo(Module::class, 'modules_id');
	}

	public function modulesstep()
	{
		return $this->belongsTo(Modulesstep::class, 'userModules_currentStep');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
