<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Assettype
 * 
 * @property int $assetTypes_id
 * @property string $assetTypes_name
 * @property int $assetCategories_id
 * @property int $manufacturers_id
 * @property int|null $instances_id
 * @property string|null $assetTypes_description
 * @property string|null $assetTypes_productLink
 * @property string|null $assetTypes_definableFields
 * @property float|null $assetTypes_mass
 * @property Carbon|null $assetTypes_inserted
 * @property int $assetTypes_dayRate
 * @property int $assetTypes_weekRate
 * @property int $assetTypes_value
 * 
 * @property Assetcategory $assetcategory
 * @property Instance|null $instance
 * @property Manufacturer $manufacturer
 * @property Collection|Asset[] $assets
 *
 * @package App\Models
 */
class Assettype extends Model
{
	protected $table = 'assettypes';
	protected $primaryKey = 'assetTypes_id';
	public $timestamps = false;

	protected $casts = [
		'assetCategories_id' => 'int',
		'manufacturers_id' => 'int',
		'instances_id' => 'int',
		'assetTypes_mass' => 'float',
		'assetTypes_dayRate' => 'int',
		'assetTypes_weekRate' => 'int',
		'assetTypes_value' => 'int'
	];

	protected $dates = [
		'assetTypes_inserted'
	];

	protected $fillable = [
		'assetTypes_name',
		'assetCategories_id',
		'manufacturers_id',
		'instances_id',
		'assetTypes_description',
		'assetTypes_productLink',
		'assetTypes_definableFields',
		'assetTypes_mass',
		'assetTypes_inserted',
		'assetTypes_dayRate',
		'assetTypes_weekRate',
		'assetTypes_value'
	];

	public function assetcategory()
	{
		return $this->belongsTo(Assetcategory::class, 'assetCategories_id');
	}

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function manufacturer()
	{
		return $this->belongsTo(Manufacturer::class, 'manufacturers_id');
	}

	public function assets()
	{
		return $this->hasMany(Asset::class, 'assetTypes_id');
	}
}
