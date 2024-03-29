<?php
$start = hrtime( true );

define( 'AXIS', dirname( $_SERVER[ 'DOCUMENT_ROOT' ] ) );
define( 'LIB',  AXIS . DIRECTORY_SEPARATOR .'lib' );
require_once( LIB . DIRECTORY_SEPARATOR .'config.php' );

$app = new Application( __DIR__ . DS .'lib', $start );
if( $app->loadRequest() ){
	$app->mode = 'map';
	$app->controllers = new RouteMap(array(
		'/generators' => '\Controllers\Generator',
		'/history' => '\Controllers\History',
		'/login'   => '\Controllers\Login',
		'/moment'  => '\Controllers\Moment',
		'/my/news' => '\Controllers\News',
		'/org'     => '\Controllers\Org',
		'/project' => '\Controllers\Project',
		'/snippet' => '\Controllers\Snippet',
		'/user'    => '\Controllers\User'
	));

	$momentController = new RegexString( '/^\/moment\/(?P<id>[0-9A-Fa-f]+)\/(?P<section>(request|response))/' );
	$app->controllers[ $momentController ] = '\Controllers\Moment';

	$app->routeRequest()->processRoute();
}
exit;


