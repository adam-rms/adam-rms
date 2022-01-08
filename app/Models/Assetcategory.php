<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Assetcategory
 * 
 * @property int $assetCategories_id
 * @property string $assetCategories_name
 * @property string|null $assetCategories_fontAwesome
 * @property int $assetCategories_rank
 * @property int $assetCategoriesGroups_id
 * @property int|null $instances_id
 * @property bool $assetCategories_deleted
 * 
 * @property Assetcategoriesgroup $assetcategoriesgroup
 * @property Instance|null $instance
 * @property Collection|Assettype[] $assettypes
 *
 * @package App\Models
 */
class Assetcategory extends Model
{
	protected $table = 'assetcategories';
	protected $primaryKey = 'assetCategories_id';
	public $timestamps = false;

	protected $casts = [
		'assetCategories_rank' => 'int',
		'assetCategoriesGroups_id' => 'int',
		'instances_id' => 'int',
		'assetCategories_deleted' => 'bool'
	];

	protected $fillable = [
		'assetCategories_name',
		'assetCategories_fontAwesome',
		'assetCategories_rank',
		'assetCategoriesGroups_id',
		'instances_id',
		'assetCategories_deleted'
	];

	public function assetcategoriesgroup()
	{
		return $this->belongsTo(Assetcategoriesgroup::class, 'assetCategoriesGroups_id');
	}

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function assettypes()
	{
		return $this->hasMany(Assettype::class, 'assetCategories_id');
	}
}
