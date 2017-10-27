<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersAndShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

		Schema::create( 'users' , function ( Blueprint $table ) {

			$table->increments( 'id' );
			$table->string( 'name' );
			$table->enum( 'role', ['employee', 'manager'] );
			$table->string( 'email' );
			$table->string( 'phone' );
			$table->timestamps( );
		});


		Schema::create( 'shifts' , function ( Blueprint $table ) {

			$table->increments( 'id' );
			$table->integer( 'manager_id' )->unsigned( );
			$table->integer( 'employee_id' )->unsigned( );
			$table->float( 'break', 8, 2 );
			$table->dateTime( 'start_time' );
			$table->dateTime( 'end_time' );
			$table->enum( 'role', ['employee', 'manager'] );
			$table->timestamps( );
			
			$table->index( [ 'start_time', 'end_time' ] );
			$table->index( [ 'end_time' ] );

			$table->foreign( 'manager_id' )
				->references( 'id' )->on( 'users' )
				->onDelete( 'restrict' );

			$table->foreign( 'employee_id' )
				->references( 'id' )->on( 'users' )
				->onDelete( 'cascade' );
		});	

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
	{
		Schema::drop( 'shifts' );
		Schema::drop( 'users' );
    }
}
