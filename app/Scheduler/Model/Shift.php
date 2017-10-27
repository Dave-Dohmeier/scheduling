<?php

namespace App\Scheduler\Model;

use Illuminate\Database\Eloquent\Model;


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

}
