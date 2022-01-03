<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Asset
 * 
 * @property int $assets_id
 * @property string|null $assets_tag
 * @property int $assetTypes_id
 * @property string|null $assets_notes
 * @property int $instances_id
 * @property string|null $asset_definableFields_1
 * @property string|null $asset_definableFields_2
 * @property string|null $asset_definableFields_3
 * @property string|null $asset_definableFields_4
 * @property string|null $asset_definableFields_5
 * @property string|null $asset_definableFields_6
 * @property string|null $asset_definableFields_7
 * @property string|null $asset_definableFields_8
 * @property string|null $asset_definableFields_9
 * @property string|null $asset_definableFields_10
 * @property Carbon $assets_inserted
 * @property int|null $assets_dayRate
 * @property int|null $assets_linkedTo
 * @property int|null $assets_weekRate
 * @property int|null $assets_value
 * @property float|null $assets_mass
 * @property bool $assets_deleted
 * @property Carbon|null $assets_endDate
 * @property string|null $assets_archived
 * @property string|null $assets_assetGroups
 * @property int|null $assets_storageLocation
 * @property bool $assets_showPublic
 * 
 * @property Asset|null $asset
 * @property Assettype $assettype
 * @property Instance $instance
 * @property Location|null $location
 * @property Collection|Asset[] $assets
 * @property Collection|Assetsassignment[] $assetsassignments
 * @property Collection|Assetsbarcode[] $assetsbarcodes
 * @property Collection|Assetsbarcodesscan[] $assetsbarcodesscans
 *
 * @package App\Models
 */
class Asset extends Model
{
	protected $table = 'assets';
	protected $primaryKey = 'assets_id';
	public $timestamps = false;

	protected $casts = [
		'assetTypes_id' => 'int',
		'instances_id' => 'int',
		'assets_dayRate' => 'int',
		'assets_linkedTo' => 'int',
		'assets_weekRate' => 'int',
		'assets_value' => 'int',
		'assets_mass' => 'float',
		'assets_deleted' => 'bool',
		'assets_storageLocation' => 'int',
		'assets_showPublic' => 'bool'
	];

	protected $dates = [
		'assets_inserted',
		'assets_endDate'
	];

	protected $fillable = [
		'assets_tag',
		'assetTypes_id',
		'assets_notes',
		'instances_id',
		'asset_definableFields_1',
		'asset_definableFields_2',
		'asset_definableFields_3',
		'asset_definableFields_4',
		'asset_definableFields_5',
		'asset_definableFields_6',
		'asset_definableFields_7',
		'asset_definableFields_8',
		'asset_definableFields_9',
		'asset_definableFields_10',
		'assets_inserted',
		'assets_dayRate',
		'assets_linkedTo',
		'assets_weekRate',
		'assets_value',
		'assets_mass',
		'assets_deleted',
		'assets_endDate',
		'assets_archived',
		'assets_assetGroups',
		'assets_storageLocation',
		'assets_showPublic'
	];

	public function asset()
	{
		return $this->belongsTo(Asset::class, 'assets_linkedTo');
	}

	public function assettype()
	{
		return $this->belongsTo(Assettype::class, 'assetTypes_id');
	}

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function location()
	{
		return $this->belongsTo(Location::class, 'assets_storageLocation');
	}

	public function assets()
	{
		return $this->hasMany(Asset::class, 'assets_linkedTo');
	}

	public function assetsassignments()
	{
		return $this->hasMany(Assetsassignment::class, 'assets_id');
	}

	public function assetsbarcodes()
	{
		return $this->hasMany(Assetsbarcode::class, 'assets_id');
	}

	public function assetsbarcodesscans()
	{
		return $this->hasMany(Assetsbarcodesscan::class, 'location_assets_id');
	}
}
