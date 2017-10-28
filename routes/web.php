<?php

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
	$router->group( [ 'prefix' => 'u/{userId:\d+}', 'middleware' => 'auth' ], 
	function ( ) use ( $router ) {

		//Simple route to get shifts
		$router->get( 'shifts', function ( $userId ) {
			return "Shifts: " . $userId;
		});
	});
});
