<?php

final class snippet extends Route {
	use \Authorized;
	use \Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors(array( 'POST','PUT' ), array('Accept,Authorization,Content-Type'));
	}

	public final function POST(){
		$this->json();

		$config = new stdClass();
		$config->required = array();
		$config->optional = array(
			"decode"        => array( 'format' => 'boolean', 'scalar' ),
			"method"        => array( 'format' => 'string', 'scalar' ),
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



		//TODO: optional, depending on Moment
		$this->authorize();


		$snippet = new \Models\Snippet( $data );
		//sets created and modified
		$snippet->validate();

		$result = $this->selectCollection( 'snippets' )->insertOne( $snippet );
		$snippet_id = $result->getInsertedId();
		$this->response->emit( array( 'snippet_id' => "{$snippet_id}" ), 201 );
	}

	public final function PUT(){
		$this->json();

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
		
		
		//TODO: optional, depending on Moment
		$this->authorize();
		
		$snippet = new Snippet( $data );
		$snippet->validate();

		$query = array(
			'_id' => $data['snippet_id' ]
		);

		$update = array(
			'$set'         => array(
				'moment_id' => $snippet->moment_id,
				'config'    => $snippet->config,
				'formatter' => $snippet->formatter
			),
			'$currentDate' => array(
				'modified' => array(
					'$type' => 'date'
				)
			)
		);
		$result = $this->selectCollection( 'snippets' )->updateOne( $query, $update );
		$response = array(
			'snippet_id'     => "{$data['snippet_id']}",
			'is_acknowledged' => $result->isAcknowledged(),
			'matches' => $result->getMatchedCount(),
			'updated' => $result->getModifiedCount()
		);
		
		$this->response->emit( $response, 201 );
	}
}
