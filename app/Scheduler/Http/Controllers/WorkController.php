<?php

namespace App\Scheduler\Http\Controllers;

use Illuminate\Http\Request;
use App\Scheduler\Http;
use App\Scheduler\Model\User;
use App\Scheduler\Model\Shift;

class WorkController extends Controller {

	/**
	 * Employees can view all upcomming shifts:
	 * Current shift and future shifts.  Completed shifts are not
	 * displayed.
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

		$shifts  =  Shift::where( 'employee_id', $user->id )
			->where( 'end_time', '>', (new \DateTime( 'now' )) )
			->orderBy( 'end_time' )->get( )->toArray( );

		return Http\ApiResponse::ok( $shifts );
	}


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
		->where( 'start_time', '>=', $shift->start_time )
		->where( 'start_time', '<', $shift->end_time )
		->orWhere( function ( $query ) use ( $shift ) {
			$query->where( 'end_time', '>', $shift->start_time )
				  ->where( 'end_time', '=<', $shift->end_time );
		})->get( )->toArray( );

		return Http\ApiResponse::ok( $withShifts );
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
}
