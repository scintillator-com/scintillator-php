<?php
//exit(0); //we are offline right now

error_reporting( E_ALL & ~E_STRICT );
ini_set( 'display_errors', 0 );
date_default_timezone_set('UTC');

if( !defined( 'CLI' ) )
	define( 'CLI', php_sapi_name() == 'cli' );

if( !defined( 'DS' ) )
	define( 'DS', DIRECTORY_SEPARATOR );

require_once( 'functions.php' );
require_once( 'autoload.php' );

$vendor = dirname( __DIR__ ) . DS .'vendor';
define( 'VENDOR', $vendor );
require( VENDOR . DS .'autoload.php' );
