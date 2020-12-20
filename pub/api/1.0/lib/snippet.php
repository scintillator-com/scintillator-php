<?php

final class snippet extends Route {
	use \Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors(array( 'POST', 'PUT' ));
	}

	public final function POST(){
		$this->authenticate()->json();

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
			'moment_id' => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'config'    => array( 'format' => 'object', 'object' => $config ),
			'formatter' => array( 'format' => 'object', 'object' => $formatter )
		);
		$this->optional = array();
		$data = $this->validate();

		$snippet = new Snippet( $data );
		$result = $this->selectCollection( 'snippets' )->insertOne( $snippet );
		$id = $result->getInsertedId();

		$this->response->code = 201;
		return array(
			'snippet_id' => "{$id}"
		);
	}

	public final function PUT(){
		$this->authenticate()->json();

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
			'snippet_id' => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'moment_id'  => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'config'     => array( 'format' => 'object', 'object' => $config ),
			'formatter'  => array( 'format' => 'object', 'object' => $formatter )
		);
		$this->optional = array();
		$data = $this->validate();
		$snippet = new Snippet( $data );
		
		$query = array(
			'_id' => new MongoDB\BSON\ObjectId( $data['snippet_id' ] )
		);

		$result = $this->selectCollection( 'snippets' )->updateOne( $query, array( '$set' => $snippet ));
		return array(
			'snippet_id'     => "{$query['_id']}",
			'is_acknowledged' => $result->isAcknowledged(),
			'matches' => $result->getMatchedCount(),
			'updated' => $result->getModifiedCount()
		);
	}
}
