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
			->orderBy( 'end_time' )->get( )->toArray();

		return Http\ApiResponse::ok( $shifts );
	}


	public function with ( Request $request ) {

		$user      =  $request->user( );
		$response  =  $this->employeeAuth( $user );
		if ( $response ) {
			return $response;
		}

		$result = $this->validate( $request, [
			'start_time' => "required|{$this->dateValidation}",
			'end_time'   => "required|{$this->dateValidation}",
		],
		[ 'date_format' => 'The :attribute is not in valid RFC 2822 format.' ] );
		
		app( )->log->info( $result );


		$start = $request->json( )->get( 'start_time' );
		$end   = $request->json( )->get( 'end_time' );

		return Http\ApiResponse::ok( 'hi?' );
	}
}
