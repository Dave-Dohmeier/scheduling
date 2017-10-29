<?php

namespace App\Scheduler\Model;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;


class User extends Model implements AuthenticatableContract, AuthorizableContract {

    use Authenticatable, Authorizable;

	const ROLE_MANAGER   =  'manager';
	const ROLE_EMPLOYEE  =  'employee';

	protected $table = 'users';

	protected $fillable = [
		'name',
		'role',
		'email',
		'phone'
	];

	protected function serializeDate( \DateTimeInterface $date ) {
		return $date->format( \DateTime::RFC2822 );
	}

}
