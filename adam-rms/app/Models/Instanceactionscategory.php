<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Instanceactionscategory
 * 
 * @property int $instanceActionsCategories_id
 * @property string $instanceActionsCategories_name
 * @property int $instanceActionsCategories_order
 * 
 * @property Collection|Instanceaction[] $instanceactions
 *
 * @package App\Models
 */
class Instanceactionscategory extends Model
{
	protected $table = 'instanceactionscategories';
	protected $primaryKey = 'instanceActionsCategories_id';
	public $timestamps = false;

	protected $casts = [
		'instanceActionsCategories_order' => 'int'
	];

	protected $fillable = [
		'instanceActionsCategories_name',
		'instanceActionsCategories_order'
	];

	public function instanceactions()
	{
		return $this->hasMany(Instanceaction::class, 'instanceActionsCategories_id');
	}
}
