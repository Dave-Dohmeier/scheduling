<?php

namespace App\Scheduler\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate extends \App\Http\Middleware\Authenticate {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	**/
	public function handle( $request, Closure $next, $guard = null ) {

		if ( $request->user( ) == null ) {
			return response( 'Unauthorized.', 401 );
		}

		return $next($request);
	}
}
