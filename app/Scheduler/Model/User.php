<?php

namespace App\Scheduler\Model;

use Illuminate\Database\Eloquent\Model;


class User extends Model {

	const ROLE_MANAGER   =  'manager';
	const ROLE_EMPLOYEE  =  'employee';

	protected $table = 'users';

	protected $fillable = [
		'name',
		'role',
		'email',
		'phone'
	];

}
