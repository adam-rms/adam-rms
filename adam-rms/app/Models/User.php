<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * 
 * @property string|null $users_username
 * @property string|null $users_name1
 * @property string|null $users_name2
 * @property int $users_userid
 * @property string|null $users_salty1
 * @property string|null $users_password
 * @property string|null $users_salty2
 * @property string $users_hash
 * @property string|null $users_email
 * @property Carbon|null $users_created
 * @property string|null $users_notes
 * @property int|null $users_thumbnail
 * @property bool $users_changepass
 * @property int|null $users_selectedProjectID
 * @property int|null $users_selectedInstanceIDLast
 * @property bool $users_suspended
 * @property bool|null $users_deleted
 * @property bool $users_emailVerified
 * @property string|null $users_social_facebook
 * @property string|null $users_social_twitter
 * @property string|null $users_social_instagram
 * @property string|null $users_social_linkedin
 * @property string|null $users_social_snapchat
 * @property string|null $users_calendarHash
 * @property string|null $users_widgets
 * @property string|null $users_notificationSettings
 * @property string|null $users_assetGroupsWatching
 * @property string|null $users_oauth_googleid
 * @property bool $users_dark_mode
 * 
 * @property Collection|Assetgroup[] $assetgroups
 * @property Collection|Assetsbarcode[] $assetsbarcodes
 * @property Collection|Assetsbarcodesscan[] $assetsbarcodesscans
 * @property Collection|Auditlog[] $auditlogs
 * @property Collection|Authtoken[] $authtokens
 * @property Collection|Cmspagesdraft[] $cmspagesdrafts
 * @property Collection|Cmspagesview[] $cmspagesviews
 * @property Collection|Crewassignment[] $crewassignments
 * @property Collection|Emailsent[] $emailsents
 * @property Collection|Emailverificationcode[] $emailverificationcodes
 * @property Collection|Locationsbarcode[] $locationsbarcodes
 * @property Collection|Maintenancejob[] $maintenancejobs
 * @property Collection|Module[] $modules
 * @property Collection|Passwordresetcode[] $passwordresetcodes
 * @property Collection|Project[] $projects
 * @property Collection|Projectsnote[] $projectsnotes
 * @property Collection|Projectsvacantrolesapplication[] $projectsvacantrolesapplications
 * @property Collection|S3file[] $s3files
 * @property Collection|Userinstance[] $userinstances
 * @property Collection|Position[] $positions
 *
 * @package App\Models
 */
class User extends Model
{
	protected $table = 'users';
	protected $primaryKey = 'users_userid';
	public $timestamps = false;

	protected $casts = [
		'users_thumbnail' => 'int',
		'users_changepass' => 'bool',
		'users_selectedProjectID' => 'int',
		'users_selectedInstanceIDLast' => 'int',
		'users_suspended' => 'bool',
		'users_deleted' => 'bool',
		'users_emailVerified' => 'bool',
		'users_dark_mode' => 'bool'
	];

	protected $dates = [
		'users_created'
	];

	protected $hidden = [
		'users_password'
	];

	protected $fillable = [
		'users_username',
		'users_name1',
		'users_name2',
		'users_salty1',
		'users_password',
		'users_salty2',
		'users_hash',
		'users_email',
		'users_created',
		'users_notes',
		'users_thumbnail',
		'users_changepass',
		'users_selectedProjectID',
		'users_selectedInstanceIDLast',
		'users_suspended',
		'users_deleted',
		'users_emailVerified',
		'users_social_facebook',
		'users_social_twitter',
		'users_social_instagram',
		'users_social_linkedin',
		'users_social_snapchat',
		'users_calendarHash',
		'users_widgets',
		'users_notificationSettings',
		'users_assetGroupsWatching',
		'users_oauth_googleid',
		'users_dark_mode'
	];

	public function assetgroups()
	{
		return $this->hasMany(Assetgroup::class, 'users_userid');
	}

	public function assetsbarcodes()
	{
		return $this->hasMany(Assetsbarcode::class, 'users_userid');
	}

	public function assetsbarcodesscans()
	{
		return $this->hasMany(Assetsbarcodesscan::class, 'users_userid');
	}

	public function auditlogs()
	{
		return $this->hasMany(Auditlog::class, 'auditLog_actionUserid');
	}

	public function authtokens()
	{
		return $this->hasMany(Authtoken::class, 'authTokens_adminId');
	}

	public function cmspagesdrafts()
	{
		return $this->hasMany(Cmspagesdraft::class, 'users_userid');
	}

	public function cmspagesviews()
	{
		return $this->hasMany(Cmspagesview::class, 'users_userid');
	}

	public function crewassignments()
	{
		return $this->hasMany(Crewassignment::class, 'users_userid');
	}

	public function emailsents()
	{
		return $this->hasMany(Emailsent::class, 'users_userid');
	}

	public function emailverificationcodes()
	{
		return $this->hasMany(Emailverificationcode::class, 'users_userid');
	}

	public function locationsbarcodes()
	{
		return $this->hasMany(Locationsbarcode::class, 'users_userid');
	}

	public function maintenancejobs()
	{
		return $this->hasMany(Maintenancejob::class, 'maintenanceJobs_user_creator');
	}

	public function modules()
	{
		return $this->belongsToMany(Module::class, 'usermodulescertifications', 'userModulesCertifications_approvedBy', 'modules_id')
					->withPivot('userModulesCertifications_id', 'users_userid', 'userModulesCertifications_revoked', 'userModulesCertifications_approvedComment', 'userModulesCertifications_timestamp');
	}

	public function passwordresetcodes()
	{
		return $this->hasMany(Passwordresetcode::class, 'users_userid');
	}

	public function projects()
	{
		return $this->hasMany(Project::class, 'projects_manager');
	}

	public function projectsnotes()
	{
		return $this->hasMany(Projectsnote::class, 'projectsNotes_userid');
	}

	public function projectsvacantrolesapplications()
	{
		return $this->hasMany(Projectsvacantrolesapplication::class, 'users_userid');
	}

	public function s3files()
	{
		return $this->hasMany(S3file::class, 'users_userid');
	}

	public function userinstances()
	{
		return $this->hasMany(Userinstance::class, 'users_userid');
	}

	public function positions()
	{
		return $this->belongsToMany(Position::class, 'userpositions', 'users_userid', 'positions_id')
					->withPivot('userPositions_id', 'userPositions_start', 'userPositions_end', 'userPositions_displayName', 'userPositions_extraPermissions', 'userPositions_show');
	}
}
