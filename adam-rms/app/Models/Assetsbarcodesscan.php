<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Assetsbarcodesscan
 * 
 * @property int $assetsBarcodesScans_id
 * @property int $assetsBarcodes_id
 * @property Carbon $assetsBarcodesScans_timestamp
 * @property int|null $users_userid
 * @property int|null $locationsBarcodes_id
 * @property int|null $location_assets_id
 * @property string|null $assetsBarcodes_customLocation
 * 
 * @property Asset|null $asset
 * @property Assetsbarcode $assetsbarcode
 * @property Locationsbarcode|null $locationsbarcode
 * @property User|null $user
 *
 * @package App\Models
 */
class Assetsbarcodesscan extends Model
{
	protected $table = 'assetsbarcodesscans';
	protected $primaryKey = 'assetsBarcodesScans_id';
	public $timestamps = false;

	protected $casts = [
		'assetsBarcodes_id' => 'int',
		'users_userid' => 'int',
		'locationsBarcodes_id' => 'int',
		'location_assets_id' => 'int'
	];

	protected $dates = [
		'assetsBarcodesScans_timestamp'
	];

	protected $fillable = [
		'assetsBarcodes_id',
		'assetsBarcodesScans_timestamp',
		'users_userid',
		'locationsBarcodes_id',
		'location_assets_id',
		'assetsBarcodes_customLocation'
	];

	public function asset()
	{
		return $this->belongsTo(Asset::class, 'location_assets_id');
	}

	public function assetsbarcode()
	{
		return $this->belongsTo(Assetsbarcode::class, 'assetsBarcodes_id');
	}

	public function locationsbarcode()
	{
		return $this->belongsTo(Locationsbarcode::class, 'locationsBarcodes_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
