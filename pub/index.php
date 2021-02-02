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
	
	
	//TODO: check auth, cancel?
	
	
	$col = $db->selectCollection( 'moments' );

	//TODO: moment
	//TODO: ignore_request

	
	$req = HTTP_Request::load()
		->setScheme( 'https' )
		->setHost( 'api.twilio.com' )
		->setPort( 443 );

	$queryLength = strlen( $req->getQueryString() );
	$headersLength = $req->measureHeaders();
	$bodyLength = $req->measureBody();

	//TODO: ignore?
	//if( $req->content_length > 1000000 )
	//	throw new Exception( "HTTP body is too large: {$this->content_length} bytes" );


	//TODO: moment->generator
	//TODO: moment->org_id
	//TODO: moment->user_id
	//TODO: moment->visibility
	//TODO: moment->timing

	$req->loadBody();


	//$request = $req->serialize();
	//$insertRes = $col->insertOne(array(
	//	'request'  => $request,
	//	'response' => new stdClass(),
	//	'timing'   => new stdClass()
	//));
	//TODO: verify


	//$insertID = $insertRes->getInsertedId();
	//Log::warning( "{$insertID}" );

	$res = $req->relayCurl();
	//$res = $req->relayStream();
print( dump( $res ) );
exit;

	exit;

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
	\Log::info( 'Duration: '.(hrtime( true ) - $start)/1000000000);
}
