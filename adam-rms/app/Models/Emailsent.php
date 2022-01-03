<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Emailsent
 * 
 * @property int $emailSent_id
 * @property int $users_userid
 * @property string $emailSent_html
 * @property string $emailSent_subject
 * @property Carbon $emailSent_sent
 * @property string $emailSent_fromEmail
 * @property string $emailSent_fromName
 * @property string $emailSent_toName
 * @property string $emailSent_toEmail
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Emailsent extends Model
{
	protected $table = 'emailsent';
	protected $primaryKey = 'emailSent_id';
	public $timestamps = false;

	protected $casts = [
		'users_userid' => 'int'
	];

	protected $dates = [
		'emailSent_sent'
	];

	protected $fillable = [
		'users_userid',
		'emailSent_html',
		'emailSent_subject',
		'emailSent_sent',
		'emailSent_fromEmail',
		'emailSent_fromName',
		'emailSent_toName',
		'emailSent_toEmail'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_userid');
	}
}
