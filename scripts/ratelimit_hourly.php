<?php
define( 'LIB', dirname( __DIR__ ) . DIRECTORY_SEPARATOR .'lib' );
require( LIB . DIRECTORY_SEPARATOR .'config.php' );

$config = Configuration::Load();
if( !empty( $config->isDeveloper ) ){
	if( defined( 'E_DEPRECATED' ) )
		set_error_handler( 'errors_as_exceptions', E_ALL & ~E_DEPRECATED );
	else
		set_error_handler( 'errors_as_exceptions', E_ALL );
}

$client = new \MongoDB\Client( $config->mongoDB['uri'] );
$db = $client->selectDatabase( $config->mongoDB['database'] );
$plans = $db->selectCollection( 'plans' );
$rate_limits = $db->selectCollection( 'rate_limits' );


foreach( $plans->find() as $plan ){
	//dump( $plan ); exit;

	$hwm = $plan->proxy_ratelimit->max - $plan->proxy_ratelimit->scheduled->increment;

	//$inc
	$query = array(
		'plan_id'         => $plan->_id,
		'proxy_evergreen' => array(
			'$lte' => $hwm
		)
	);

	$inc = array(
		'$currentDate' => array(
			'modified' => array(
				'$type' => 'date'
			)
		),
		'$inc' => array(
			'proxy_evergreen' => $plan->proxy_ratelimit->scheduled->increment
		)
	);
	$res = $rate_limits->updateMany( $query, $inc );
	echo "\$inc( {$plan->name} ): {$res->getModifiedCount()}". PHP_EOL;


	//$set
	$query = array(
		'plan_id'         => $plan->_id,
		'proxy_evergreen' => array(
			'$gt' => $hwm,
			'$lt'  => $plan->proxy_ratelimit->max
		)
	);

	$set = array(
		'$currentDate' => array(
			'modified' => array(
				'$type' => 'date'
			)
		),
		'$set' => array(
			'proxy_evergreen' => $plan->proxy_ratelimit->max
		)
	);
	$res = $rate_limits->updateMany( $query, $set );
	echo "\$set( {$plan->name} ): {$res->getModifiedCount()}". PHP_EOL;
}
