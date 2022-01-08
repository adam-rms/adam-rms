<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Instance
 * 
 * @property int $instances_id
 * @property string $instances_name
 * @property bool|null $instances_deleted
 * @property string|null $instances_plan
 * @property string|null $instances_address
 * @property string|null $instances_phone
 * @property string|null $instances_email
 * @property string|null $instances_website
 * @property string|null $instances_weekStartDates
 * @property int|null $instances_logo
 * @property int|null $instances_emailHeader
 * @property string|null $instances_termsAndPayment
 * @property string|null $instances_quoteTerms
 * @property int $instances_storageLimit
 * @property float|null $instances_config_linkedDefaultDiscount
 * @property string $instances_config_currency
 * @property string|null $instances_cableColours
 * @property string|null $instances_publicConfig
 * 
 * @property Collection|Assetcategory[] $assetcategories
 * @property Collection|Assetgroup[] $assetgroups
 * @property Collection|Asset[] $assets
 * @property Collection|Assetsassignmentsstatus[] $assetsassignmentsstatuses
 * @property Collection|Assettype[] $assettypes
 * @property Collection|Client[] $clients
 * @property Collection|Cmspage[] $cmspages
 * @property Collection|Instanceposition[] $instancepositions
 * @property Collection|Location[] $locations
 * @property Collection|Maintenancejobsstatus[] $maintenancejobsstatuses
 * @property Collection|Manufacturer[] $manufacturers
 * @property Collection|Module[] $modules
 * @property Collection|Project[] $projects
 * @property Collection|Projectstype[] $projectstypes
 * @property Collection|S3file[] $s3files
 * @property Collection|Signupcode[] $signupcodes
 *
 * @package App\Models
 */
class Instance extends Model
{
	protected $table = 'instances';
	protected $primaryKey = 'instances_id';
	public $timestamps = false;

	protected $casts = [
		'instances_deleted' => 'bool',
		'instances_logo' => 'int',
		'instances_emailHeader' => 'int',
		'instances_storageLimit' => 'int',
		'instances_config_linkedDefaultDiscount' => 'float'
	];

	protected $fillable = [
		'instances_name',
		'instances_deleted',
		'instances_plan',
		'instances_address',
		'instances_phone',
		'instances_email',
		'instances_website',
		'instances_weekStartDates',
		'instances_logo',
		'instances_emailHeader',
		'instances_termsAndPayment',
		'instances_quoteTerms',
		'instances_storageLimit',
		'instances_config_linkedDefaultDiscount',
		'instances_config_currency',
		'instances_cableColours',
		'instances_publicConfig'
	];

	public function assetcategories()
	{
		return $this->hasMany(Assetcategory::class, 'instances_id');
	}

	public function assetgroups()
	{
		return $this->hasMany(Assetgroup::class, 'instances_id');
	}

	public function assets()
	{
		return $this->hasMany(Asset::class, 'instances_id');
	}

	public function assetsassignmentsstatuses()
	{
		return $this->hasMany(Assetsassignmentsstatus::class, 'instances_id');
	}

	public function assettypes()
	{
		return $this->hasMany(Assettype::class, 'instances_id');
	}

	public function clients()
	{
		return $this->hasMany(Client::class, 'instances_id');
	}

	public function cmspages()
	{
		return $this->hasMany(Cmspage::class, 'instances_id');
	}

	public function instancepositions()
	{
		return $this->hasMany(Instanceposition::class, 'instances_id');
	}

	public function locations()
	{
		return $this->hasMany(Location::class, 'instances_id');
	}

	public function maintenancejobsstatuses()
	{
		return $this->hasMany(Maintenancejobsstatus::class, 'instances_id');
	}

	public function manufacturers()
	{
		return $this->hasMany(Manufacturer::class, 'instances_id');
	}

	public function modules()
	{
		return $this->hasMany(Module::class, 'instances_id');
	}

	public function projects()
	{
		return $this->hasMany(Project::class, 'instances_id');
	}

	public function projectstypes()
	{
		return $this->hasMany(Projectstype::class, 'instances_id');
	}

	public function s3files()
	{
		return $this->hasMany(S3file::class, 'instances_id');
	}

	public function signupcodes()
	{
		return $this->hasMany(Signupcode::class, 'instances_id');
	}
}
