<?php

require( '/home/lc/sites/scintillator-php/vendor/autoload.php' );

final class snippet extends Route {
	public final function OPTIONS(){
		header( 'HTTP/1.1 204 No Content', true, 204 );
		//header( 'Access-Control-Allow-Credentials: true');
		header( 'Access-Control-Allow-Headers: Accept,Content-Type' );
		header( 'Access-Control-Allow-Methods: POST,PUT' );
		
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

	public final function POST(){
		$this->response->formatter = new Formatter_JSON();
		//$this->response->headers[ 'Access-Control-Allow-Credentials' ] = 'true';
		$this->response->headers[ 'Access-Control-Allow-Origin' ] = '*';
		if( !empty( $_SERVER['HTTP_ORIGIN'] ) )
			$this->response->headers[ 'Access-Control-Allow-Origin' ] = $_SERVER['HTTP_ORIGIN'];


		if( !$this->request->isContentType( 'application/json' ) )
			throw new Exception( "Unsupported content type: {$this->request->headers['content-type']}", 422 );

		$this->request->data = $this->request->getParseJSON();
		if( empty( $this->request->data ) )
			throw new Exception( "Can't parse data", 422 );


		$config = new stdClass();
		$config->required = array(
		);
		$config->optional = array(
			"method"        => array( 'format' => 'string', 'scalar' ),
			"decode"        => array( 'format' => 'boolean', 'scalar' ),
			"body_params"   => array( 'format' => 'string', 'default' => array(), 'array' ),
			"header_params" => array( 'format' => 'string', 'default' => array(), 'array' ),
			"query_params"  => array( 'format' => 'string', 'default' => array(), 'array' )
		);


		$formatter = new stdClass();
		$formatter->required = array(
			"name"     => array( 'format' => 'string', 'scalar' ),
			"language" => array( 'format' => 'string', 'scalar' ),
			"library"  => array( 'format' => 'string', 'scalar' )
		);
		$formatter->optional = array(
		);

		$this->required = array(
			'moment_id' => array( 'format' => 'hex', 'scalar' ),
			'config'    => array( 'format' => 'object', 'object' => $config ),
			'formatter' => array( 'format' => 'object', 'object' => $formatter )
		);
		$this->optional = array();
		$snippet = $this->validate();
		$snippet[ 'moment_id' ] = new MongoDB\BSON\ObjectId( $snippet[ 'moment_id' ] );

		$this->_client = new MongoDB\Client( 'mongodb://192.168.1.31:27017' );
		$result = $this->_client->selectDatabase( 'scintillator' )
			->selectCollection( 'snippets' )
			->insertOne( $snippet );
		$id = $result->getInsertedId();

		$this->response->code = 201;
		return array(
			'_id' => "{$id}"
		);
	}

	public final function PUT(){
		$this->response->formatter = new Formatter_JSON();
		//$this->response->headers[ 'Access-Control-Allow-Credentials' ] = 'true';
		$this->response->headers[ 'Access-Control-Allow-Origin' ] = '*';
		if( !empty( $_SERVER['HTTP_ORIGIN'] ) )
			$this->response->headers[ 'Access-Control-Allow-Origin' ] = $_SERVER['HTTP_ORIGIN'];


		if( !$this->request->isContentType( 'application/json' ) )
			throw new Exception();

		$this->request->data = $this->request->getParseJSON();
		if( empty( $this->request->data ) )
			throw new Exception();


		$config = new stdClass();
		$config->required = array();
		$config->optional = array(
			"method"        => array( 'format' => 'string', 'scalar' ),
			"decode"        => array( 'format' => 'boolean', 'scalar' ),
			"body_params"   => array( 'format' => 'string', 'default' => array(), 'array' ),
			"header_params" => array( 'format' => 'string', 'default' => array(), 'array' ),
			"query_params"  => array( 'format' => 'string', 'default' => array(), 'array' )
		);

		$formatter = new stdClass();
		$formatter->required = array(
			"name"     => array( 'format' => 'string', 'scalar' ),
			"language" => array( 'format' => 'string', 'scalar' ),
			"library"  => array( 'format' => 'string', 'scalar' )
		);
		$formatter->optional = array();

		$this->required = array(
			'_id'       => array( 'format' => 'hex', 'scalar' ),
			'moment_id' => array( 'format' => 'hex', 'scalar' ),
			'config'    => array( 'format' => 'object', 'object' => $config ),
			'formatter' => array( 'format' => 'object', 'object' => $formatter )
		);
		$this->optional = array();
		$snippet = $this->validate();
		$snippet[ 'moment_id' ] = new MongoDB\BSON\ObjectId( $snippet[ 'moment_id' ] );
		
		$query = array(
			'_id' => new MongoDB\BSON\ObjectId( $snippet[ '_id' ] )
		);
		unset( $snippet['_id'] );


		$this->_client = new MongoDB\Client( 'mongodb://192.168.1.31:27017' );
		$result = $this->_client->selectDatabase( 'scintillator' )
			->selectCollection( 'snippets' )
			->updateOne( $query, array( '$set' => $snippet ));

		return array(
			'_id'     => "{$query['_id']}",
			'isAcknowledged' => $result->isAcknowledged(),
			'matches' => $result->getMatchedCount(),
			'updated' => $result->getModifiedCount()
		);
	}
}
