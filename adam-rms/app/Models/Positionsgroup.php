<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Positionsgroup
 * 
 * @property int $positionsGroups_id
 * @property string $positionsGroups_name
 * @property string|null $positionsGroups_actions
 *
 * @package App\Models
 */
class Positionsgroup extends Model
{
	protected $table = 'positionsgroups';
	protected $primaryKey = 'positionsGroups_id';
	public $timestamps = false;

	protected $fillable = [
		'positionsGroups_name',
		'positionsGroups_actions'
	];
}
