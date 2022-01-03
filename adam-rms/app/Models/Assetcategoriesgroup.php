<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Assetcategoriesgroup
 * 
 * @property int $assetCategoriesGroups_id
 * @property string $assetCategoriesGroups_name
 * @property string|null $assetCategoriesGroups_fontAwesome
 * @property int $assetCategoriesGroups_order
 * 
 * @property Collection|Assetcategory[] $assetcategories
 *
 * @package App\Models
 */
class Assetcategoriesgroup extends Model
{
	protected $table = 'assetcategoriesgroups';
	protected $primaryKey = 'assetCategoriesGroups_id';
	public $timestamps = false;

	protected $casts = [
		'assetCategoriesGroups_order' => 'int'
	];

	protected $fillable = [
		'assetCategoriesGroups_name',
		'assetCategoriesGroups_fontAwesome',
		'assetCategoriesGroups_order'
	];

	public function assetcategories()
	{
		return $this->hasMany(Assetcategory::class, 'assetCategoriesGroups_id');
	}
}
