<?php

namespace App\Scheduler\Http\Controllers;


use Illuminate\Http\Request;
use App\Scheduler\Model\User;
use App\Scheduler\Http;

class Controller extends \App\Http\Controllers\Controller {


	protected function employeeAuth ( User $user ) {

		if ( $user->role != User::ROLE_EMPLOYEE ) {
			return Http\ApiResponse::forbid( );
		}

		return null;
	}


	protected function managerAuth ( User $user ) {

		if ( ! $user || ! ( $user instanceof User ) ) {
			return Http\ApiResponse::notAuth( );
		}
		if ( $user->role != User::ROLE_MANAGER ) {
			return Http\ApiResponse::forbid( );
		}

		return null;
	}
}
