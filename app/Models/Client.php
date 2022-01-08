<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Client
 * 
 * @property int $clients_id
 * @property string $clients_name
 * @property int $instances_id
 * @property bool $clients_deleted
 * @property string|null $clients_website
 * @property string|null $clients_email
 * @property string|null $clients_notes
 * @property string|null $clients_address
 * @property string|null $clients_phone
 * 
 * @property Instance $instance
 * @property Collection|Location[] $locations
 * @property Collection|Project[] $projects
 *
 * @package App\Models
 */
class Client extends Model
{
	protected $table = 'clients';
	protected $primaryKey = 'clients_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'clients_deleted' => 'bool'
	];

	protected $fillable = [
		'clients_name',
		'instances_id',
		'clients_deleted',
		'clients_website',
		'clients_email',
		'clients_notes',
		'clients_address',
		'clients_phone'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function locations()
	{
		return $this->hasMany(Location::class, 'clients_id');
	}

	public function projects()
	{
		return $this->hasMany(Project::class, 'clients_id');
	}
}
