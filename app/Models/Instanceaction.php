<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Instanceaction
 * 
 * @property int $instanceActions_id
 * @property string $instanceActions_name
 * @property int $instanceActionsCategories_id
 * @property string|null $instanceActions_dependent
 * @property string|null $instanceActions_incompatible
 * 
 * @property Instanceactionscategory $instanceactionscategory
 *
 * @package App\Models
 */
class Instanceaction extends Model
{
	protected $table = 'instanceactions';
	protected $primaryKey = 'instanceActions_id';
	public $timestamps = false;

	protected $casts = [
		'instanceActionsCategories_id' => 'int'
	];

	protected $fillable = [
		'instanceActions_name',
		'instanceActionsCategories_id',
		'instanceActions_dependent',
		'instanceActions_incompatible'
	];

	public function instanceactionscategory()
	{
		return $this->belongsTo(Instanceactionscategory::class, 'instanceActionsCategories_id');
	}
}
