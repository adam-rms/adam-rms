<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cmspagesdraft
 * 
 * @property int $cmsPagesDrafts_id
 * @property int $cmsPages_id
 * @property int|null $users_userid
 * @property Carbon $cmsPagesDrafts_timestamp
 * @property array|null $cmsPagesDrafts_data
 * @property string|null $cmsPagesDrafts_changelog
 * @property int $cmsPagesDrafts_revisionID
 * 
 * @property Cmspage $cmspage
 * @property User|null $user
 *
 * @package App\Models
 */
class Cmspagesdraft extends Model
{
	protected $table = 'cmspagesdrafts';
	protected $primaryKey = 'cmsPagesDrafts_id';
	public $timestamps = false;

	protected $casts = [
		'cmsPages_id' => 'int',
		'users_userid' => 'int',
		'cmsPagesDrafts_data' => 'json',
		'cmsPagesDrafts_revisionID' => 'int'
	];

	protected $dates = [
		'cmsPagesDrafts_timestamp'
	];

	protected $fillable = [
		'cmsPages_id',
		'users_userid',
		'cmsPagesDrafts_timestamp',
		'cmsPagesDrafts_data',
		'cmsPagesDrafts_changelog',
		'cmsPagesDrafts_revisionID'
	];

	public function cmspage()
	{
		return $this->belongsTo(Cmspage::class, 'cmsPages_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
