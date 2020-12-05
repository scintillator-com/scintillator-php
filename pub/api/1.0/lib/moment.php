<?php

require( '/home/lc/sites/scintillator-php/vendor/autoload.php' );

final class moment extends Route {
	public final function GET(){
		$this->response->formatter = new Formatter_JSON();
		//$this->response->headers[ 'Access-Control-Allow-Credentials' ] = 'true';
		$this->response->headers[ 'Access-Control-Allow-Origin' ] = '*';
		if( !empty( $_SERVER['HTTP_ORIGIN'] ) )
			$this->response->headers[ 'Access-Control-Allow-Origin' ] = $_SERVER['HTTP_ORIGIN'];

		if( empty( $this->request->urlArgs ) )
			throw new Exception( "The 'id' URL parameter is required", 422 );

		if( count( $this->request->urlArgs ) !== 1 )
			throw new Exception( "Too many URL arguments, expected 1", 422 );


		$this->required = array(
			'id' => array( 'format' => 'string' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = new MongoDB\BSON\ObjectId( $data['id'] );

		$response = null;
		$config = Configuration::Load();
		$client = new MongoDB\Client( $config->mongoDB['uri'] );
		$moment = $client->selectDatabase( 'scintillator' )->selectCollection( 'moments' )->findOne( $query );
		$response = \Models\Moment::formatDetail( $moment );
		return $response;
	}

	public final function OPTIONS(){
		header( 'HTTP/1.1 204 No Content', true, 204 );
		//header( 'Access-Control-Allow-Credentials: true');
		//header( 'Access-Control-Allow-Headers: Content-Type' );
		header( 'Access-Control-Allow-Methods: GET,HEAD' );
		
		if( !empty( $_SERVER['HTTP_ORIGIN'] ) )
			header( "Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}" );
		else
			header( "Access-Control-Allow-Origin: *" );
		exit;

		//$this->response->formatter = new Formatter_None();
		//$this->response->headers[ 'Access-Control-Allow-Credentials' ] = 'true';
		//$this->response->headers[ 'Access-Control-Allow-Headers' ] = 'Content-Type';
		//$this->response->headers[ 'Access-Control-Allow-Methods' ] = 'GET,HEAD';
		//$this->response->headers[ 'Access-Control-Allow-Origin' ] = $_SERVER['HTTP_ORIGIN'];
	}
}
