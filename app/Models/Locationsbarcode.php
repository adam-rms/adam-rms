<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Locationsbarcode
 * 
 * @property int $locationsBarcodes_id
 * @property int $locations_id
 * @property string $locationsBarcodes_value
 * @property string $locationsBarcodes_type
 * @property string|null $locationsBarcodes_notes
 * @property Carbon $locationsBarcodes_added
 * @property int|null $users_userid
 * @property bool|null $locationsBarcodes_deleted
 * 
 * @property User|null $user
 * @property Collection|Assetsbarcodesscan[] $assetsbarcodesscans
 *
 * @package App\Models
 */
class Locationsbarcode extends Model
{
	protected $table = 'locationsbarcodes';
	protected $primaryKey = 'locationsBarcodes_id';
	public $timestamps = false;

	protected $casts = [
		'locations_id' => 'int',
		'users_userid' => 'int',
		'locationsBarcodes_deleted' => 'bool'
	];

	protected $dates = [
		'locationsBarcodes_added'
	];

	protected $fillable = [
		'locations_id',
		'locationsBarcodes_value',
		'locationsBarcodes_type',
		'locationsBarcodes_notes',
		'locationsBarcodes_added',
		'users_userid',
		'locationsBarcodes_deleted'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}

	public function assetsbarcodesscans()
	{
		return $this->hasMany(Assetsbarcodesscan::class, 'locationsBarcodes_id');
	}
}
