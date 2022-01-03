<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Location
 * 
 * @property int $locations_id
 * @property string $locations_name
 * @property int|null $clients_id
 * @property int $instances_id
 * @property string|null $locations_address
 * @property bool $locations_deleted
 * @property int|null $locations_subOf
 * @property string|null $locations_notes
 * 
 * @property Client|null $client
 * @property Instance $instance
 * @property Location|null $location
 * @property Collection|Asset[] $assets
 * @property Collection|Location[] $locations
 * @property Collection|Project[] $projects
 *
 * @package App\Models
 */
class Location extends Model
{
	protected $table = 'locations';
	protected $primaryKey = 'locations_id';
	public $timestamps = false;

	protected $casts = [
		'clients_id' => 'int',
		'instances_id' => 'int',
		'locations_deleted' => 'bool',
		'locations_subOf' => 'int'
	];

	protected $fillable = [
		'locations_name',
		'clients_id',
		'instances_id',
		'locations_address',
		'locations_deleted',
		'locations_subOf',
		'locations_notes'
	];

	public function client()
	{
		return $this->belongsTo(Client::class, 'clients_id');
	}

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function location()
	{
		return $this->belongsTo(Location::class, 'locations_subOf');
	}

	public function assets()
	{
		return $this->hasMany(Asset::class, 'assets_storageLocation');
	}

	public function locations()
	{
		return $this->hasMany(Location::class, 'locations_subOf');
	}

	public function projects()
	{
		return $this->hasMany(Project::class, 'locations_id');
	}
}
