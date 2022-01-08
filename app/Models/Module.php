<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Module
 * 
 * @property int $modules_id
 * @property int $instances_id
 * @property int $users_userid
 * @property string $modules_name
 * @property string|null $modules_description
 * @property string|null $modules_learningObjectives
 * @property bool $modules_deleted
 * @property bool $modules_show
 * @property int|null $modules_thumbnail
 * @property bool $modules_type
 * 
 * @property Instance $instance
 * @property S3file|null $s3file
 * @property User $user
 * @property Collection|Modulesstep[] $modulessteps
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Module extends Model
{
	protected $table = 'modules';
	protected $primaryKey = 'modules_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'users_userid' => 'int',
		'modules_deleted' => 'bool',
		'modules_show' => 'bool',
		'modules_thumbnail' => 'int',
		'modules_type' => 'bool'
	];

	protected $fillable = [
		'instances_id',
		'users_userid',
		'modules_name',
		'modules_description',
		'modules_learningObjectives',
		'modules_deleted',
		'modules_show',
		'modules_thumbnail',
		'modules_type'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function s3file()
	{
		return $this->belongsTo(S3file::class, 'modules_thumbnail');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}

	public function modulessteps()
	{
		return $this->hasMany(Modulesstep::class, 'modules_id');
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'usermodulescertifications', 'modules_id', 'userModulesCertifications_approvedBy')
					->withPivot('userModulesCertifications_id', 'users_userid', 'userModulesCertifications_revoked', 'userModulesCertifications_approvedComment', 'userModulesCertifications_timestamp');
	}
}
