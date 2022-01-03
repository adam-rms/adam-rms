<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cmspagesview
 * 
 * @property int $cmsPagesViews_id
 * @property int $cmsPages_id
 * @property Carbon $cmsPagesViews_timestamp
 * @property int|null $users_userid
 * @property bool $cmsPages_type
 * 
 * @property Cmspage $cmspage
 * @property User|null $user
 *
 * @package App\Models
 */
class Cmspagesview extends Model
{
	protected $table = 'cmspagesviews';
	protected $primaryKey = 'cmsPagesViews_id';
	public $timestamps = false;

	protected $casts = [
		'cmsPages_id' => 'int',
		'users_userid' => 'int',
		'cmsPages_type' => 'bool'
	];

	protected $dates = [
		'cmsPagesViews_timestamp'
	];

	protected $fillable = [
		'cmsPages_id',
		'cmsPagesViews_timestamp',
		'users_userid',
		'cmsPages_type'
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
