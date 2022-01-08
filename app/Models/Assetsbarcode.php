<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Assetsbarcode
 * 
 * @property int $assetsBarcodes_id
 * @property int|null $assets_id
 * @property string $assetsBarcodes_value
 * @property string $assetsBarcodes_type
 * @property string|null $assetsBarcodes_notes
 * @property Carbon $assetsBarcodes_added
 * @property int|null $users_userid
 * @property bool|null $assetsBarcodes_deleted
 * 
 * @property Asset|null $asset
 * @property User|null $user
 * @property Collection|Assetsbarcodesscan[] $assetsbarcodesscans
 *
 * @package App\Models
 */
class Assetsbarcode extends Model
{
	protected $table = 'assetsbarcodes';
	protected $primaryKey = 'assetsBarcodes_id';
	public $timestamps = false;

	protected $casts = [
		'assets_id' => 'int',
		'users_userid' => 'int',
		'assetsBarcodes_deleted' => 'bool'
	];

	protected $dates = [
		'assetsBarcodes_added'
	];

	protected $fillable = [
		'assets_id',
		'assetsBarcodes_value',
		'assetsBarcodes_type',
		'assetsBarcodes_notes',
		'assetsBarcodes_added',
		'users_userid',
		'assetsBarcodes_deleted'
	];

	public function asset()
	{
		return $this->belongsTo(Asset::class, 'assets_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}

	public function assetsbarcodesscans()
	{
		return $this->hasMany(Assetsbarcodesscan::class, 'assetsBarcodes_id');
	}
}
