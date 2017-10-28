<?php

namespace App\Scheduler\Providers;

use App\Scheduler\Model\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends \App\Providers\AuthServiceProvider {

	/**
	 * Register any application services.
	 *
	 * @return void
	**/
	public function register ( ) {
		//
	}

	/**
	 * Boot the authentication services for the application.
	 *
	 * @return void
	**/
	public function boot ( ) {

		$this->app['auth']->viaRequest( 'api', function ( $request ) {

			// Where does this magic 2 come from?  Loading route parameters attached
			// to a request should be better defined. Should this be trusted in an 
			// authentication protocol?  Probably Not.
			$userId = array_get( $request->route()[2], 'userId', null );
			if ( $userId ) {
				return User::find( $userId );
			}
			return null;
		});
	}
}
