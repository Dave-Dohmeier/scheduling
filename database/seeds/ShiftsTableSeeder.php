<?php

use Illuminate\Database\Seeder;
use App\Scheduler\Model\User;
use App\Scheduler\Model\Shift;

class ShiftsTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$manager   =  User::where( 'role', User::ROLE_MANAGER )->first( );
		$employee  =  User::where( 'role', User::ROLE_EMPLOYEE )->first( );

		$start  =  new \DateTime( 'now' );
		$end    =  clone $start;
		$end->modify( '+8 hours' );

		Shift::create([
			'manager_id' => $manager->id,
			'employee_id' => $employee->id,
			'break' => 15.00,
			'start_time' => $start,
			'end_time' => $end
		]);

	}
}
