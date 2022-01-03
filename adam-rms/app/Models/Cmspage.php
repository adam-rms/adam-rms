<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cmspage
 * 
 * @property int $cmsPages_id
 * @property int $instances_id
 * @property bool $cmsPages_showNav
 * @property bool $cmsPages_showPublic
 * @property bool $cmsPages_showPublicNav
 * @property string|null $cmsPages_visibleToGroups
 * @property int $cmsPages_navOrder
 * @property string|null $cmsPages_fontAwesome
 * @property string $cmsPages_name
 * @property string|null $cmsPages_description
 * @property bool $cmsPages_archived
 * @property bool $cmsPages_deleted
 * @property Carbon $cmsPages_added
 * @property int|null $cmsPages_subOf
 * 
 * @property Cmspage|null $cmspage
 * @property Instance $instance
 * @property Collection|Cmspage[] $cmspages
 * @property Collection|Cmspagesdraft[] $cmspagesdrafts
 * @property Collection|Cmspagesview[] $cmspagesviews
 *
 * @package App\Models
 */
class Cmspage extends Model
{
	protected $table = 'cmspages';
	protected $primaryKey = 'cmsPages_id';
	public $timestamps = false;

	protected $casts = [
		'instances_id' => 'int',
		'cmsPages_showNav' => 'bool',
		'cmsPages_showPublic' => 'bool',
		'cmsPages_showPublicNav' => 'bool',
		'cmsPages_navOrder' => 'int',
		'cmsPages_archived' => 'bool',
		'cmsPages_deleted' => 'bool',
		'cmsPages_subOf' => 'int'
	];

	protected $dates = [
		'cmsPages_added'
	];

	protected $fillable = [
		'instances_id',
		'cmsPages_showNav',
		'cmsPages_showPublic',
		'cmsPages_showPublicNav',
		'cmsPages_visibleToGroups',
		'cmsPages_navOrder',
		'cmsPages_fontAwesome',
		'cmsPages_name',
		'cmsPages_description',
		'cmsPages_archived',
		'cmsPages_deleted',
		'cmsPages_added',
		'cmsPages_subOf'
	];

	public function cmspage()
	{
		return $this->belongsTo(Cmspage::class, 'cmsPages_subOf');
	}

	public function instance()
	{
		return $this->belongsTo(Instance::class, 'instances_id');
	}

	public function cmspages()
	{
		return $this->hasMany(Cmspage::class, 'cmsPages_subOf');
	}

	public function cmspagesdrafts()
	{
		return $this->hasMany(Cmspagesdraft::class, 'cmsPages_id');
	}

	public function cmspagesviews()
	{
		return $this->hasMany(Cmspagesview::class, 'cmsPages_id');
	}
}
