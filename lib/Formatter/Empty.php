<?php

class Formatter_Empty extends Formatter{
	//From base:
	$this->code = 204;

	public final function emit( &$data, $code=null ){
		if( $code )
			$this->code = $code;

		header( 'HTTP/1.1 204 No Content', true, $this->code );
	}

	public final function format( &$data ){}

	public final function formatData( &$data ){}

	public final function formatException( Exception &$exception ){}
}
