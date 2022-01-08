<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Userposition
 * 
 * @property int $userPositions_id
 * @property int|null $users_userid
 * @property Carbon $userPositions_start
 * @property Carbon|null $userPositions_end
 * @property int|null $positions_id
 * @property string|null $userPositions_displayName
 * @property string|null $userPositions_extraPermissions
 * @property bool $userPositions_show
 * 
 * @property Position|null $position
 * @property User|null $user
 *
 * @package App\Models
 */
class Userposition extends Model
{
	protected $table = 'userpositions';
	protected $primaryKey = 'userPositions_id';
	public $timestamps = false;

	protected $casts = [
		'users_userid' => 'int',
		'positions_id' => 'int',
		'userPositions_show' => 'bool'
	];

	protected $dates = [
		'userPositions_start',
		'userPositions_end'
	];

	protected $fillable = [
		'users_userid',
		'userPositions_start',
		'userPositions_end',
		'positions_id',
		'userPositions_displayName',
		'userPositions_extraPermissions',
		'userPositions_show'
	];

	public function position()
	{
		return $this->belongsTo(Position::class, 'positions_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
