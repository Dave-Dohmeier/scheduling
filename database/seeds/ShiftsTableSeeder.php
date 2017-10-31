<?php

use Illuminate\Database\Seeder;
use App\Scheduler\Model\User;
use App\Scheduler\Model\Shift;

/**
 * Seed the shifts table.  Expects users to exist already.
 *
 * @author Dave Dohmeier <david.dohmeier@gmail.com>
 **/
class ShiftsTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$manager    =  User::where( 'role', User::ROLE_MANAGER )->first( );
		$employees  =  User::where( 'role', User::ROLE_EMPLOYEE )->get( );

		$start  =  new \DateTime( 'now' );
		for( $i = 0; $i < 20; $i++ ) { 
			foreach ( $employees as $employee ) {
				$end    =  clone $start;
				$hours  = rand( 1, 10 );
				$end->modify( "+{$hours} hours" );

				Shift::create([
					'manager_id' => $manager->id,
					'employee_id' => $employee->id,
					'break' => 15.00,
					'start_time' => $start,
					'end_time' => $end
				]);
			}

			$nextdelay = rand( 16, 32 );
			$start->modify( "+{$nextdelay} hours" );
		}
	}
}
