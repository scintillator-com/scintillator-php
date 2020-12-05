<?php

function __autoload_service( $class ){
	$path = __DIR__ . DS . class_to_path( $class );
	if( file_exists( $path ) ){
		include( $path );
		return true;
	}
	else{
		return false;
	}
}

function class_to_path( $class ){
	static $find, $replace;
	if( empty( $replace ) ){
		$find = array( '/_([_]*)?/', '/\\\\/' );
		$replace = DS === '\\' ? '\\\$1' : '/$1';
	}

	$path = preg_replace( $find, $replace, $class );
	if( $path && $path[0] == DS ){
		$path[0] = '_';
	}

	return "{$path}.php";
}

spl_autoload_register( '__autoload_service' );

