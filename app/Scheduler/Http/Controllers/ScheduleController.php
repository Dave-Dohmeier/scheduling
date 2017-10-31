<?php

namespace App\Scheduler\Http\Controllers;

use Illuminate\Http\Request;
use App\Scheduler\Http;
use App\Scheduler\Model\User;
use App\Scheduler\Model\Shift;


/**
 * Scheduling Control Class.  Provides functionality
 * for roles responsable for assigning and managing shifts.
 *
 * @package \App\Scheduler\Http\Controllers
 * @author Dave Dohmeier <david.dohmeier@gmail.com>
 **/
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
		    );
		$shifts  =  $this->addOverlap( $shifts, $start, $end )->get( );

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

		$employees  =  User::where( 'role', '=', User::ROLE_EMPLOYEE )
			->orderBy( 'id' )->get( )->toArray( );

		return Http\ApiResponse::ok( $employees );
	}


	/**
	 * Managers can create a new shift.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function add ( Request $request ) {

		$user      =  $request->user( );
		$response  =  $this->managerAuth( $user );
		if ( $response ) {
			return $response;
		}

		$result = $this->validate( $request, [
			'start_time'   =>  "required|{$this->dateValidation}",
			'end_time'     =>  "required|{$this->dateValidation}",
			'employee_id'  =>  "required|integer",
			'break'        =>  "nullable|numeric",
		],
		[ 'date_format' => 'The :attribute is not in valid RFC 2822 format.' ] );

		$start       =  new \DateTime( $request->input( 'start_time' ) );
		$end         =  new \DateTime( $request->input( 'end_time' ) );
		$employeeId  =  (int) $request->input( 'employee_id' );
		$break       =  $request->input( 'break' ) ?: 0.0;

		$employee  =  User::find( $employeeId );
		if ( ! $employee ) {
			return Http\ApiResponse::invalid( 
				[ 'employee_id' => 'Employee could not be located.' ]
			);
		}

		if ( $end <= $start ) {
			return Http\ApiResponse::invalid( 
				[ 'end_time' => 'End time must be greater than the start time.' ]
			);
		}

		$overlap  =  Shift::where( 'employee_id', '=', $employee->id );
		$overlap  =  $this->addOverlap( $overlap, $start, $end )->first( );

		if( $overlap ) {
			return Http\ApiResponse::invalid( 
				[ 'conflict' => 'Employee has a scheduling conflict with the provided time range.' ]
			);
		}

		$shift = Shift::create([
			'manager_id' => $user->id,
			'employee_id' => $employee->id,
			'break' => $break,
			'start_time' => $start,
			'end_time' => $end
		]);
		app( )->log->info( "New Shift Id({$shift->id}) created by Manager Id({$user->id})." );

		return Http\ApiResponse::ok( $shift->toArray( ) );
	}


	/**
	 * Managers can modify an existing shift.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function modify ( Request $request ) {

		$user      =  $request->user( );
		$response  =  $this->managerAuth( $user );
		if ( $response ) {
			return $response;
		}

		$result = $this->validate( $request, [
			'shift_id'     =>  "nullable|integer",
			'start_time'   =>  "nullable|{$this->dateValidation}",
			'end_time'     =>  "nullable|{$this->dateValidation}",
			'employee_id'  =>  "nullable|integer",
		],
		[ 'date_format' => 'The :attribute is not in valid RFC 2822 format.' ] );

		$shift  =  Shift::find( $request->input( 'shift_id' ) );
		if ( ! $shift ) {
			return Http\ApiResponse::invalid( 
				[ 'shift_id' => 'Shift could not be located.' ]
			);
		}

		if ( $request->input( 'start_time' ) ) {
			$shift->start_time = new \DateTime( $request->input( 'start_time' ) );
		}
		if ( $request->input( 'end_time' ) ) {
			$shift->start_time = new \DateTime( $request->input( 'end_time' ) );
		}
		if ( $request->input( 'employee_id' ) ) {
			$employee  =  User::find( $request->input( 'employee_id' ) );
			if ( ! $employee ) {
				return Http\ApiResponse::invalid( 
					[ 'employee_id' => 'Employee could not be located.' ]
				);
			}

			$shift->employee_id  =  $employee->id;
		}

		//Unclear if we should update the manager Id here?  Lets go ahead and do it
		$shift->manager_id  =  $user->id;

		//Verfy that there will not be a scheduling conflict for a different shift
		$overlap = Shift::where( 'employee_id', '=', $employee->id )
		->where( 'id', '<>', $shift->id );
		$overlap  =  $this->addOverlap( $overlap, $shift->start_time, $shift->end_time )->first( );

		if( $overlap ) {
			return Http\ApiResponse::invalid( 
				[ 'conflict' => 'Employee has a scheduling conflict with the provided time range.' ]
			);
		}
		
		$shift->save( );
		app( )->log->info( "Shift Id({$shift->id}) modified by Manager Id({$user->id})." );

		return Http\ApiResponse::ok( $shift->toArray( ) );
	}

}
