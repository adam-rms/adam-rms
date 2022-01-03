<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Assetgroup
 * 
 * @property int $assetGroups_id
 * @property string $assetGroups_name
 * @property string|null $assetGroups_description
 * @property bool $assetGroups_deleted
 * @property int|null $users_userid
 * @property int $instances_id
 * 
 * @property Instance $instance
 * @property User|null $user
 *
 * @package App\Models
 */
class Assetgroup extends Model
{
	protected $table = 'assetgroups';
	protected $primaryKey = 'assetGroups_id';
	public $timestamps = false;

	protected $casts = [
		'assetGroups_deleted' => 'bool',
		'users_userid' => 'int',
		'instances_id' => 'int'
	];

	protected $fillable = [
		'assetGroups_name',
		'assetGroups_description',
		'assetGroups_deleted',
		'users_userid',
		'instances_id'
	];

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
