<?php
$start = hrtime( true );

define( 'AXIS', dirname( $_SERVER[ 'DOCUMENT_ROOT' ] ) );
define( 'LIB',  AXIS . DIRECTORY_SEPARATOR .'lib' );
require_once( LIB . DIRECTORY_SEPARATOR .'config.php' );

$app = new Application( __DIR__ . DS .'lib', $start );
if( $app->loadRequest() ){
	$app->routeRequest()->processRoute();
}
exit;
