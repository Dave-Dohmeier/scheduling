<?php

namespace App\Scheduler\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Shift Model -- Tracks who and when an employee is working
 * as well as the manager that assigned them the shift.
 *
 * @package \App\Scheduler\Model
 * @author Dave Dohmeier <david.dohmeier@gmail.com>
 **/
class Shift extends Model {

	protected $table = 'shifts';

	protected $dates = [ 'start_time', 'end_time' ];

	protected $fillable = [
		'manager_id',
		'employee_id',
		'break',
		'start_time',
		'end_time'
	];


	/**
	 * Alter the format for all date serialization.
	 * This impacts how dates will be displayed in the API
	 * but is not used for the database translation layer.
	 *
	 * @param  \DateTimeInterface  $date
	 * @return string Formatted Date String
	 */
	protected function serializeDate( \DateTimeInterface $date ) {
		return $date->format( \DateTime::RFC2822 );
	}
}
