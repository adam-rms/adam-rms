<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Signupcode
 * 
 * @property int $signupCodes_id
 * @property string $signupCodes_name
 * @property int $instances_id
 * @property bool $signupCodes_deleted
 * @property bool $signupCodes_valid
 * @property string|null $signupCodes_notes
 * @property string $signupCodes_role
 * @property int|null $instancePositions_id
 * 
 * @property Instanceposition|null $instanceposition
 * @property Instance $instance
 * @property Collection|Userinstance[] $userinstances
 *
 * @package App\Models
 */
class Signupcode extends Model
{
	protected $table = 'signupcodes';
	protected $primaryKey = 'signupCodes_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'signupCodes_deleted' => 'bool',
		'signupCodes_valid' => 'bool',
		'instancePositions_id' => 'int'
	];

	protected $fillable = [
		'signupCodes_name',
		'instances_id',
		'signupCodes_deleted',
		'signupCodes_valid',
		'signupCodes_notes',
		'signupCodes_role',
		'instancePositions_id'
	];

	public function instanceposition()
	{
		return $this->belongsTo(Instanceposition::class, 'instancePositions_id');
	}

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function userinstances()
	{
		return $this->hasMany(Userinstance::class, 'signupCodes_id');
	}
}
