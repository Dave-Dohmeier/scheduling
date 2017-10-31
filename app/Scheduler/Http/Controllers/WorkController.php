<?php

namespace App\Scheduler\Http\Controllers;

use Illuminate\Http\Request;
use App\Scheduler\Http;
use App\Scheduler\Model\User;
use App\Scheduler\Model\Shift;

/**
 * Work Control Class.  Provides functionality
 * for finding out when you are working and who
 * you are working with.
 *
 * @package \App\Scheduler\Http\Controllers
 * @author Dave Dohmeier <david.dohmeier@gmail.com>
 **/
class WorkController extends Controller {

	/**
	 * Employees can view all upcoming shifts:
	 * Current shift and future shifts.  Completed shifts are not
	 * displayed.  Each shift indicates the manager for that shift
	 * and their contact information.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function when ( Request $request ) {

		$user      =  $request->user( );
		$response  =  $this->employeeAuth( $user );
		if ( $response ) {
			return $response;
		}
		
		$shifts  =  Shift::join( 'users', function ( $join ) {
			$join->on( 'shifts.manager_id', '=', 'users.id' );
		})
		->select( 'shifts.*', 'users.name', 'users.phone', 'users.email' )
		->where( 'employee_id', $user->id )
		->where( 'end_time', '>', (new \DateTime( 'now' )) )
		->orderBy( 'end_time' )->get( );

		$result = [];
		foreach ( $shifts as $shift ) {
			$entry = [];
			$entry['id']          =  $shift->id;
			$entry['start_time']  =  $shift->start_time->format( \DateTime::RFC2822 );
			$entry['end_time']    =  $shift->end_time->format( \DateTime::RFC2822 );
			$entry['manager']     =  [ 'name' => $shift->name, 'email' => $shift->email, 'phone' => $shift->phone ];
			$result[]  =  $entry;
		}
		return Http\ApiResponse::ok( $result );
	}


	/**
	 * Allow Employees to see who they are working with on a given shift.
	 *
	 * @param  Request  $request
	 * @param  int  $shiftId
	 * @return Response
	 */
	public function with ( Request $request, $shiftId ) {

		$user      =  $request->user( );
		$response  =  $this->employeeAuth( $user );
		if ( $response ) {
			return $response;
		}

		// Should employees be able to see shifts that they are not working on?
		// Assume yes for now it seems convenient if you want to ask to swap
		$shift = Shift::find( $shiftId );
		if ( ! $shift ) {
			return Http\ApiResponse::missing( );
		}

		$withShifts = Shift::join( 'users', function ( $join ) use ( $user, $shift ) {
			$join->on( 'shifts.employee_id', '=', 'users.id' );
			$join->where( 'shifts.employee_id', '<>', $user->id );
		})
		->select( 'shifts.*', 'users.name', 'users.phone', 'users.email' );
		$withShifts  =  $this->addOverlap( $withShifts, $shift->start_time, $shift->end_time )->get( );

		$entries = [];
		foreach ( $withShifts as $withShift ) {
			$entry = [];
			$entry['name']        =  $withShift->name;
			$entry['email']       =  $withShift->email;
			$entry['phone']       =  $withShift->phone;
			$entry['start_time']  =  $shift->start_time->format( \DateTime::RFC2822 );
			$entry['end_time']    =  $shift->end_time->format( \DateTime::RFC2822 );
			$entries[]  =  $entry;
		}
		return Http\ApiResponse::ok( [ 'id' => $shift->id, 'with' => $entries ] );
	}


	/**
	 * Employees can get a summary of all completed shifts
	 * by week. Hours are assigned to the week that the shift ended
	 * if it crosses weeks.
	 *
	 * This could possibly take in a year parameter to speed it up
	 * and simplfy sorting but for now thre isn't time.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function summary ( Request $request ) {

		$user      =  $request->user( );
		$response  =  $this->employeeAuth( $user );
		if ( $response ) {
			return $response;
		}

		$shifts  =  Shift::where( 'employee_id', $user->id )
			->where( 'end_time', '>', (new \DateTime( 'now' )) )
			->orderBy( 'end_time' )->get( );

		$report = [];
		foreach ( $shifts as $shift ) {

			$hours  =  $shift->start_time->diffInHours( $shift->end_time );
			$year   =  $shift->end_time->year;
			$week   =  $shift->end_time->weekOfYear;

			if ( ! isset ( $report[ $year ] ) ) {
				$report[ $year ] = [ ];
			}
			if ( ! isset ( $report[ $year ][ $week ] ) ) {
				$report[ $year ][ $week ]  =  $hours;
			}
			else {
				$report[ $year ][ $week ]  +=  $hours;
			}
		}

		return Http\ApiResponse::ok( $report );
	}

}
