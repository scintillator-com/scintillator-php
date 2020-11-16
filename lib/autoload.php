<?php

function __autoload_service( $class ){
	$path = dirname( __FILE__ ) . DS . class_to_path( $class );
	if( file_exists( $path ) ){
		include( $path );
		return true;
	}
	else{
		return false;
	}
}

function class_to_path( $class ){
	static $replace;
	if( empty( $replace ) ){
		$replace = DS == '/' ? '/$1' : '\\\$1';
	}

	$path = preg_replace( '/_([_]*)?/', $replace, $class );
	if( $path && $path[0] == DS ){
		$path[0] = '_';
	}

	return $path .'.php';
}

spl_autoload_register( '__autoload_service' );

