<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Position
 * 
 * @property int $positions_id
 * @property string $positions_displayName
 * @property string|null $positions_positionsGroups
 * @property int $positions_rank
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Position extends Model
{
	protected $table = 'positions';
	protected $primaryKey = 'positions_id';
	public $timestamps = false;

	protected $casts = [
		'positions_rank' => 'int'
	];

	protected $fillable = [
		'positions_displayName',
		'positions_positionsGroups',
		'positions_rank'
	];

	public function users()
	{
		return $this->belongsToMany(User::class, 'userpositions', 'positions_id', 'users_userid')
					->withPivot('userPositions_id', 'userPositions_start', 'userPositions_end', 'userPositions_displayName', 'userPositions_extraPermissions', 'userPositions_show');
	}
}
