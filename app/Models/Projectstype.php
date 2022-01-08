<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Projectstype
 * 
 * @property int $projectsTypes_id
 * @property string $projectsTypes_name
 * @property int $instances_id
 * @property bool $projectsTypes_deleted
 * @property bool $projectsTypes_config_finance
 * @property int $projectsTypes_config_files
 * @property int $projectsTypes_config_assets
 * @property int $projectsTypes_config_client
 * @property int $projectsTypes_config_venue
 * @property int $projectsTypes_config_notes
 * @property int $projectsTypes_config_crew
 * 
 * @property Instance $instance
 *
 * @package App\Models
 */
class Projectstype extends Model
{
	protected $table = 'projectstypes';
	protected $primaryKey = 'projectsTypes_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'projectsTypes_deleted' => 'bool',
		'projectsTypes_config_finance' => 'bool',
		'projectsTypes_config_files' => 'int',
		'projectsTypes_config_assets' => 'int',
		'projectsTypes_config_client' => 'int',
		'projectsTypes_config_venue' => 'int',
		'projectsTypes_config_notes' => 'int',
		'projectsTypes_config_crew' => 'int'
	];

	protected $fillable = [
		'projectsTypes_name',
		'instances_id',
		'projectsTypes_deleted',
		'projectsTypes_config_finance',
		'projectsTypes_config_files',
		'projectsTypes_config_assets',
		'projectsTypes_config_client',
		'projectsTypes_config_venue',
		'projectsTypes_config_notes',
		'projectsTypes_config_crew'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}
}
