<?php

use Illuminate\Database\Seeder;
use App\Scheduler\Model\User;

/**
 * Seed the users table.
 *
 * @author Dave Dohmeier <david.dohmeier@gmail.com>
 **/
class UsersTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		User::create([
			'name' => 'Bob',
			'role'  => User::ROLE_MANAGER,
			'email' => 'bob@fakewiw.com'
		]);

		User::create([
			'name' => 'Ralph',
			'role'  => User::ROLE_EMPLOYEE,
			'email' => 'ralph@fakewiw.com'
		]);

		User::create([
			'name' => 'Toby',
			'role'  => User::ROLE_EMPLOYEE,
			'email' => 'toby@fakewiw.com'
		]);	
	}
}
