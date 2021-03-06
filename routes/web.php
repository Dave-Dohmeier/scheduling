<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//API will run on a simple api endpoint (no versioning)
$router->group( [ 'prefix' => 'api' ], function ( ) use ( $router ) { 

	//API does not support authentication so simulating user identification
	$router->group( [ 
		'prefix' => 'u/{userId:\d+}',
		'middleware' => 'auth',
		'namespace' => '\\App\\Scheduler\\Http\\Controllers' ], function ( ) use ( $router ) {

			//Route grouping for employees to query when they work
			$router->group( [ 'prefix' => 'work' ], function ( ) use ( $router ) {
				$router->get( 'when', [ 'uses' => 'WorkController@when' ] );
				$router->get( 'with/{shiftId:\d+}', [ 'uses' => 'WorkController@with' ] );
				$router->get( 'summary', [ 'uses' => 'WorkController@summary' ] );
			});

			//Route grouping for shift mamangers to manage the schedule
			$router->group( [ 'prefix' => 'schedule' ], function ( ) use ( $router ) {
				$router->get( 'shift/list', [ 'uses' => 'ScheduleController@list' ] );
				$router->get( 'employee/list', [ 'uses' => 'ScheduleController@employees' ] );
				$router->post( 'shift', [ 'uses' => 'ScheduleController@add' ] );
				$router->put( 'shift', [ 'uses' => 'ScheduleController@modify' ] );
			});
	});
});
