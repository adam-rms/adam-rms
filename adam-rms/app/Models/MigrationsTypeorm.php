<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MigrationsTypeorm
 * 
 * @property int $id
 * @property int $timestamp
 * @property string $name
 *
 * @package App\Models
 */
class MigrationsTypeorm extends Model
{
	protected $table = 'migrations_typeorm';
	public $timestamps = false;

	protected $casts = [
		'timestamp' => 'int'
	];

	protected $fillable = [
		'timestamp',
		'name'
	];
}
