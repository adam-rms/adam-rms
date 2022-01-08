<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Payment
 * 
 * @property int $payments_id
 * @property int $payments_amount
 * @property int $payments_quantity
 * @property bool $payments_type
 * @property string|null $payments_reference
 * @property Carbon $payments_date
 * @property string|null $payments_supplier
 * @property string|null $payments_method
 * @property string|null $payments_comment
 * @property int $projects_id
 * @property bool $payments_deleted
 * 
 * @property Project $project
 *
 * @package App\Models
 */
class Payment extends Model
{
	protected $table = 'payments';
	protected $primaryKey = 'payments_id';
	public $timestamps = false;

	protected $casts = [
		'payments_amount' => 'int',
		'payments_quantity' => 'int',
		'payments_type' => 'bool',
		'projects_id' => 'int',
		'payments_deleted' => 'bool'
	];

	protected $dates = [
		'payments_date'
	];

	protected $fillable = [
		'payments_amount',
		'payments_quantity',
		'payments_type',
		'payments_reference',
		'payments_date',
		'payments_supplier',
		'payments_method',
		'payments_comment',
		'projects_id',
		'payments_deleted'
	];

	public function project()
	{
		return $this->belongsTo(Project::class, 'projects_id');
	}
}
