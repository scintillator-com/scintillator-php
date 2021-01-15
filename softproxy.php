<?php
try{
	$start = hrtime( true );
	define( 'LIB', dirname( $_SERVER[ 'DOCUMENT_ROOT' ] ) . DIRECTORY_SEPARATOR .'lib' );
	require_once( LIB . DIRECTORY_SEPARATOR .'config.php' );

	$config = Configuration::Load();
	if( !empty( $config->isDeveloper ) ){
		if( defined( 'E_DEPRECATED' ) ){
			set_error_handler( 'errors_as_exceptions', E_ALL & ~E_DEPRECATED );
		}else{
			set_error_handler( 'errors_as_exceptions', E_ALL );
		}
	}

	$client = new \MongoDB\Client( $config->mongoDB['uri'] );
	$db = $client->selectDatabase( 'scintillator' );
	
	
	//TODO: check auth
	
	
	$col = $db->selectCollection( 'moments' );
	
	$req = HTTP_Request::load()
		->setScheme( 'https' )
		->setHost( 'api.twilio.com' )
		->setPort( 443 );

print( dump( $req->serialize() ) );
exit;

	if( $req->content_length > 1000000 )
		throw new Exception( "HTTP body is too large: {$this->content_length} bytes" );

	$req->loadBody();

	//if( $req->isHostSelf() )
	//	throw new Exception( 'Refusing to proxy request to self' );


	//TODO: check supported verbs
	//if( !$req->isSupported() )
	//	throw new Exception( "Unsupported verb: {$req->_verb}" );

	#Log::info( "{$req}" ); exit;



	Log::info( "{$req}" );
	exit;

	//$request = $req->serialize();
	//$insertRes = $col->insertOne(array(
	//	'request'  => $request,
	//	'response' => new stdClass(),
	//	'timing'   => new stdClass()
	//));
	//TODO: verify


	//$insertID = $insertRes->getInsertedId();
	//Log::warning( "{$insertID}" );

	$res = $req->relayCurl(array(
		'scheme' => 'https',
		'host'   => 'api.twilio.com'
	));

	//$res = $req->relayStream();
	//Log::info( "{$res}" );
	//exit;

	$response = $res->serialize();
	$updateRes = $col->updateOne(
		array( '_id' => $insertID ), //condition
		array( '$set' => array( 
			'response' => $response,
			//TODO: record timing
			//'timing'   => $res->getTiming()
		))
	);


	header( $res->getStatusHeader(), true, $res->getStatusCode() );
	foreach( $res->getHeaders() as &$header ){
		header( "{$header['key']}: {$header['value']}" );
	}

	echo $res->getBody(); exit;
}
catch( Exception $ex ){
	Log::warning( "{$ex}" );

	header( 'Content-Type: text/plain' );
	echo 'Oops!';
}
finally{
	\Log::info( 'Duration: '.(hrtime( true ) - self::$start)/1000000000);
}