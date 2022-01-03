<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Action
 * 
 * @property int $actions_id
 * @property string $actions_name
 * @property int $actionsCategories_id
 * @property string|null $actions_dependent
 * @property string|null $actions_incompatible
 * 
 * @property Actionscategory $actionscategory
 *
 * @package App\Models
 */
class Action extends Model
{
	protected $table = 'actions';
	protected $primaryKey = 'actions_id';
	public $timestamps = false;

	protected $casts = [
		'actionsCategories_id' => 'int'
	];

	protected $fillable = [
		'actions_name',
		'actionsCategories_id',
		'actions_dependent',
		'actions_incompatible'
	];

	public function actionscategory()
	{
		return $this->belongsTo(Actionscategory::class, 'actionsCategories_id');
	}
}
