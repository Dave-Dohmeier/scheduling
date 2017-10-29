<?php

namespace App\Scheduler\Http\Controllers;

use Illuminate\Http\Request;
use App\Scheduler\Http;
use App\Scheduler\Model\User;
use App\Scheduler\Model\Shift;

class ScheduleController extends Controller {

	/**
	 * Managers can list all shifts between a time range:
	 * start_date and end_date are required in RFC 2822 format.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function list ( Request $request ) {

		$user      =  $request->user( );
		$response  =  $this->managerAuth( $user );
		if ( $response ) {
			return $response;
		}

		$result = $this->validate( $request, [
			'start_time' => "required|{$this->dateValidation}",
			'end_time'   => "required|{$this->dateValidation}",
		],
		[ 'date_format' => 'The :attribute is not in valid RFC 2822 format.' ] );

		$start  =  new \DateTime( $request->input( 'start_time' ) );
		$end    =  new \DateTime( $request->input( 'end_time' ) );


		$shifts  =  Shift::join( 'users as e', 'shifts.employee_id', '=', 'e.id' )
			->join( 'users as m', 'shifts.employee_id', '=', 'm.id' )
			->select( 
				'shifts.*',
				'e.name as employee_name',
				'e.phone as employee_phone',
				'e.email as employee_email',
				'm.name as manager_name',
				'm.phone as manager_phone',
				'm.email as manager_email'
		    )
		->where( 'start_time', '>=', $start )
		->where( 'start_time', '<', $end )
		->orWhere( function ( $query ) use ( $start, $end ) {
			$query->where( 'end_time', '>', $start )
				  ->where( 'end_time', '=<', $end );
		})->get( );

		$entries = [];
		foreach ( $shifts as $shift ) {
			$entry = [];
			$entry['id']          =  $shift->id;
			$entry['start_time']  =  $shift->start_time->format( \DateTime::RFC2822 );
			$entry['end_time']    =  $shift->end_time->format( \DateTime::RFC2822 );
			$entry['manager']     =  [ 
				'id'    => $shift->manager_id,
				'name'  => $shift->manager_name,
				'email' => $shift->manager_email,
				'phone' => $shift->manager_phone
			];
			$entry['employee']     =  [ 
				'id'    => $shift->employee_id,
				'name'  => $shift->employee_name,
				'email' => $shift->employee_email,
				'phone' => $shift->employee_phone
			];
			$entries[]  =  $entry;
		}
		return Http\ApiResponse::ok( $entries );
	}


	/**
	 * Managers can list all employees
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function employees ( Request $request ) {

		$user      =  $request->user( );
		$response  =  $this->managerAuth( $user );
		if ( $response ) {
			return $response;
		}

		$shifts  =  Shift::join( 'users as e', 'shifts.employee_id', '=', 'e.id' )
			->join( 'users as m', 'shifts.employee_id', '=', 'm.id' )
			->select( 
				'shifts.*',
				'e.name as employee_name',
				'e.phone as employee_phone',
				'e.email as employee_email',
				'm.name as manager_name',
				'm.phone as manager_phone',
				'm.email as manager_email'
		    )
		->where( 'start_time', '>=', $start )
		->where( 'start_time', '<', $end )
		->orWhere( function ( $query ) use ( $start, $end ) {
			$query->where( 'end_time', '>', $start )
				  ->where( 'end_time', '=<', $end );
		})->get( );

		$entries = [];
		foreach ( $shifts as $shift ) {
			$entry = [];
			$entry['id']          =  $shift->id;
			$entry['start_time']  =  $shift->start_time->format( \DateTime::RFC2822 );
			$entry['end_time']    =  $shift->end_time->format( \DateTime::RFC2822 );
			$entry['manager']     =  [ 
				'id'    => $shift->manager_id,
				'name'  => $shift->manager_name,
				'email' => $shift->manager_email,
				'phone' => $shift->manager_phone
			];
			$entry['employee']     =  [ 
				'id'    => $shift->employee_id,
				'name'  => $shift->employee_name,
				'email' => $shift->employee_email,
				'phone' => $shift->employee_phone
			];
			$entries[]  =  $entry;
		}
		return Http\ApiResponse::ok( $entries );
	}


	/**
	 * Allow Employees to see who they are working with on a given shift.
	 *
	 * @param  Request  $request
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
		->select( 'shifts.*', 'users.name', 'users.phone', 'users.email' )
		->where( 'start_time', '>=', $shift->start_time )
		->where( 'start_time', '<', $shift->end_time )
		->orWhere( function ( $query ) use ( $shift ) {
			$query->where( 'end_time', '>', $shift->start_time )
				  ->where( 'end_time', '=<', $shift->end_time );
		})->get( );

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
		/*
		$result = $this->validate( $request, [
			'start_time' => "required|{$this->dateValidation}",
			'end_time'   => "required|{$this->dateValidation}",
		],
		[ 'date_format' => 'The :attribute is not in valid RFC 2822 format.' ] );

		$start  =  $request->input( 'start_time' );
		$end    =  $request->input( 'end_time' );
		
		app( )->log->info( $shiftId );

		return Http\ApiResponse::ok( 'hi?' );
		 */
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
			app( )->log->info( print_r( [ $hours, $year, $week ], true ) );
		}

		return Http\ApiResponse::ok( $report );
	}

}
