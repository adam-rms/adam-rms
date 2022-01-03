<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Manufacturer
 * 
 * @property int $manufacturers_id
 * @property string $manufacturers_name
 * @property int|null $instances_id
 * @property string|null $manufacturers_internalAdamRMSNote
 * @property string|null $manufacturers_website
 * @property string|null $manufacturers_notes
 * 
 * @property Instance|null $instance
 * @property Collection|Assettype[] $assettypes
 *
 * @package App\Models
 */
class Manufacturer extends Model
{
	protected $table = 'manufacturers';
	protected $primaryKey = 'manufacturers_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int'
	];

	protected $fillable = [
		'manufacturers_name',
		'instances_id',
		'manufacturers_internalAdamRMSNote',
		'manufacturers_website',
		'manufacturers_notes'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function assettypes()
	{
		return $this->hasMany(Assettype::class, 'manufacturers_id');
	}
}
