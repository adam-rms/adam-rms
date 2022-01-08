<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Instanceposition
 * 
 * @property int $instancePositions_id
 * @property int $instances_id
 * @property string $instancePositions_displayName
 * @property int $instancePositions_rank
 * @property string|null $instancePositions_actions
 * @property bool $instancePositions_deleted
 * 
 * @property Instance $instance
 * @property Collection|Signupcode[] $signupcodes
 * @property Collection|Userinstance[] $userinstances
 *
 * @package App\Models
 */
class Instanceposition extends Model
{
	protected $table = 'instancepositions';
	protected $primaryKey = 'instancePositions_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'instancePositions_rank' => 'int',
		'instancePositions_deleted' => 'bool'
	];

	protected $fillable = [
		'instances_id',
		'instancePositions_displayName',
		'instancePositions_rank',
		'instancePositions_actions',
		'instancePositions_deleted'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function signupcodes()
	{
		return $this->hasMany(Signupcode::class, 'instancePositions_id');
	}

	public function userinstances()
	{
		return $this->hasMany(Userinstance::class, 'instancePositions_id');
	}
}
