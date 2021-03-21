<?php
//$start = microtime( true );
$start = hrtime( true );

define( 'AXIS', dirname( $_SERVER[ 'DOCUMENT_ROOT' ] ) );
define( 'LIB',  AXIS . DIRECTORY_SEPARATOR .'lib' );
require_once( LIB . DIRECTORY_SEPARATOR .'config.php' );

Application::init( __DIR__ . DS .'lib', $start );
if( Application::loadRequest() ){
	Application::routeRequest();
	Application::processRoute();
}
exit;
