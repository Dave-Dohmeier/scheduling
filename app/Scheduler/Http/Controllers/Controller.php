<?php

namespace App\Scheduler\Http\Controllers;

use Illuminate\Http\Request;
use App\Scheduler\Model\User;
use App\Scheduler\Http;


/**
 * Base controller class. Provides functionality for basic role 
 * based authentication and validation.
 *
 * @package \App\Scheduler\Http\Controllers
 * @author Dave Dohmeier <david.dohmeier@gmail.com>
 **/
class Controller extends \App\Http\Controllers\Controller {

	protected $dateValidation  =  'date_format:"' . \DateTime::RFC2822 . '"';

	/**
	 * Check to see if a user is an employee.
	 *
	 * @param  User  $request
	 * @return Response|null
	 */
	protected function employeeAuth ( User $user ) {

		if ( $user->role != User::ROLE_EMPLOYEE ) {
			return Http\ApiResponse::forbid( );
		}

		return null;
	}

	/**
	 * Check to see if a user is a manager.
	 *
	 * @param  User  $request
	 * @return Response|null
	 */
	protected function managerAuth ( User $user ) {

		if ( ! $user || ! ( $user instanceof User ) ) {
			return Http\ApiResponse::notAuth( );
		}
		if ( $user->role != User::ROLE_MANAGER ) {
			return Http\ApiResponse::forbid( );
		}

		return null;
	}


	/**
	 * Utility method to verify if a start and end time overlap for a shift.
	 * Overlap Rules:
	 * - Overlapping start is the same as or up to before the end of the shift.
	 * - Overlapping end is after the start or any time up to the same end time as the shift.
	 *
	 * @todo: This should be a trait.
	 *
	 * @param  mixed $query  The query to add start and end time overlap checking to.
	 * @return Response|null
	 */
	protected function addOverlap ( $query, \DateTime $start, \DateTime $end ) {

		$query->where( function ( $query ) use ( $start, $end ) {
			$query->where( function ( $query ) use ( $start, $end ) {
				$query->where( 'start_time', '>=', $start )
					->where( 'start_time', '<', $end );
			})
			->orWhere( function ( $query ) use ( $start, $end ) {
				$query->where( 'end_time', '>', $start )
					->where( 'end_time', '=<', $end );
			});
		});

		return $query;
	}
}
