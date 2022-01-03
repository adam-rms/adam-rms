<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Actionscategory
 * 
 * @property int $actionsCategories_id
 * @property string $actionsCategories_name
 * @property int|null $actionsCategories_order
 * 
 * @property Collection|Action[] $actions
 *
 * @package App\Models
 */
class Actionscategory extends Model
{
	protected $table = 'actionscategories';
	protected $primaryKey = 'actionsCategories_id';
	public $timestamps = false;

	protected $casts = [
		'actionsCategories_order' => 'int'
	];

	protected $fillable = [
		'actionsCategories_name',
		'actionsCategories_order'
	];

	public function actions()
	{
		return $this->hasMany(Action::class, 'actionsCategories_id');
	}
}
