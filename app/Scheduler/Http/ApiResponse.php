<?php

namespace App\Scheduler\Http;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse {

	public function __construct ( ) {

	}

	public static function ok ( $data, $message = 'success', $status = Response::HTTP_OK ) {

		return response( )->json( 
			[ 'data' => $data, 'message' => $message ],
			$status
		);
	}

	public static function error ( $status, $message, $data = null ) {

		if ( $data === null ) {
			return response( )->json( [ 'message' => $message ], $status );
		}
		return response( )->json( 
			[ 'data' => $data, 'message' => $message ],
			$status 
		);
	}

	public static function forbid ( $message = 'Forbidden' ) {

		return self::error( Response::HTTP_FORBIDDEN, $message );
	}

	public static function notAuth ( $message = 'Unauthorized' ) {

		return self::error( Response::HTTP_UNAUTHORIZED, $message );
	}

	public static function missing ( $message = 'Not Found' ) {

		return self::error( Response::HTTP_NOT_FOUND, $message );
	}

	public static function invalid ( $data, $message = 'Invalid' ) {

		return self::error( Response::HTTP_UNPROCESSABLE_ENTITY, $message, $data );
	}
}
