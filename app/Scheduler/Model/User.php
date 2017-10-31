<?php

namespace App\Scheduler\Model;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * User Model -- Supports authentication and authorization.
 *
 * @package \App\Scheduler\Model
 * @author Dave Dohmeier <david.dohmeier@gmail.com>
 **/
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
