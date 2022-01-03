<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class S3file
 * 
 * @property int $s3files_id
 * @property int $instances_id
 * @property string|null $s3files_path
 * @property string|null $s3files_name
 * @property string $s3files_filename
 * @property string $s3files_extension
 * @property string|null $s3files_original_name
 * @property string $s3files_region
 * @property string $s3files_endpoint
 * @property string|null $s3files_cdn_endpoint
 * @property string $s3files_bucket
 * @property int $s3files_meta_size
 * @property bool $s3files_meta_public
 * @property string|null $s3files_shareKey
 * @property int $s3files_meta_type
 * @property int|null $s3files_meta_subType
 * @property Carbon $s3files_meta_uploaded
 * @property int|null $users_userid
 * @property Carbon|null $s3files_meta_deleteOn
 * @property bool $s3files_meta_physicallyStored
 * @property bool $s3files_compressed
 * 
 * @property Instance $instance
 * @property User|null $user
 * @property Collection|Maintenancejobsmessage[] $maintenancejobsmessages
 * @property Collection|Module[] $modules
 *
 * @package App\Models
 */
class S3file extends Model
{
	protected $table = 's3files';
	protected $primaryKey = 's3files_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		's3files_meta_size' => 'int',
		's3files_meta_public' => 'bool',
		's3files_meta_type' => 'int',
		's3files_meta_subType' => 'int',
		'users_userid' => 'int',
		's3files_meta_physicallyStored' => 'bool',
		's3files_compressed' => 'bool'
	];

	protected $dates = [
		's3files_meta_uploaded',
		's3files_meta_deleteOn'
	];

	protected $fillable = [
		'instances_id',
		's3files_path',
		's3files_name',
		's3files_filename',
		's3files_extension',
		's3files_original_name',
		's3files_region',
		's3files_endpoint',
		's3files_cdn_endpoint',
		's3files_bucket',
		's3files_meta_size',
		's3files_meta_public',
		's3files_shareKey',
		's3files_meta_type',
		's3files_meta_subType',
		's3files_meta_uploaded',
		'users_userid',
		's3files_meta_deleteOn',
		's3files_meta_physicallyStored',
		's3files_compressed'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}

	public function maintenancejobsmessages()
	{
		return $this->hasMany(Maintenancejobsmessage::class, 'maintenanceJobsMessages_file');
	}

	public function modules()
	{
		return $this->hasMany(Module::class, 'modules_thumbnail');
	}
}
