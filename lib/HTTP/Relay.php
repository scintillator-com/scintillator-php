<?php

//namespace HTTP;

abstract class HTTP_Relay{
	public static function create( $type ){
		switch( $type ){
			case 'CURL':
				require_once( 'Relay/Curl.php' );
				return new HTTP_Relay_Curl();

			default:
				throw new Exception( "Relay not implemented: {$type}" );
		}
	}
}
