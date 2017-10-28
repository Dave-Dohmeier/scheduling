<?php

namespace App\Scheduler\Http;

class ApiResponse {

	public function __construct ( ) {

	}

	public static function ok ( $data, $message = 'success', $status = 200 ) {

		return response( )->json( 
			[ 'data' => $data, 'message' => $message ],
			$status
		);
	}

	public static function error ( $status, $message ) {

		return response( )->json( [ 'message' => $message ], $status );
	}

	public static function forbid ( $message = 'Forbidden' ) {

		return self::error( 403, $message );
	}

	public static function notAuth ( $message = 'Unauthorized' ) {

		return self::error( 401, $message );
	}
}
