<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Phinxlog
 * 
 * @property int $version
 * @property string|null $migration_name
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property bool $breakpoint
 *
 * @package App\Models
 */
class Phinxlog extends Model
{
	protected $table = 'phinxlog';
	protected $primaryKey = 'version';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'version' => 'int',
		'breakpoint' => 'bool'
	];

	protected $dates = [
		'start_time',
		'end_time'
	];

	protected $fillable = [
		'migration_name',
		'start_time',
		'end_time',
		'breakpoint'
	];
}
