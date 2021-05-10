<?php

namespace Controllers;

final class Snippet extends \Route {
	use \Traits\Authorized;

	public final function __construct( \Request $request ){
		parent::__construct( $request );

		$this->setHandler( 'POST', array( $this, 'POST' ), array( 'Accept', 'Authorization', 'Content-Type' ) );
		$this->setHandler( 'PUT',  array( $this, 'PUT' ),  array( 'Accept', 'Authorization', 'Content-Type' ) );
	}

	protected final function POST(){
		$this->json();

		$this->required = array(
			'moment_id' => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'config'    => array( 'format' => 'object', 'object' => (object)array(
				'required' => array(),
				'optional' => array(
					'decode'        => array( 'format' => 'boolean', 'scalar' ),
					'method'        => array( 'format' => 'string', 'scalar' ),
					'body_params'   => array( 'format' => 'string', 'default' => array(), 'array' ),
					'header_params' => array( 'format' => 'string', 'default' => array(), 'array' ),
					'query_params'  => array( 'format' => 'string', 'default' => array(), 'array' )
				)
			)),
			'formatter' => array( 'format' => 'object', 'object' => (object)array(
				'required' => array(
					'name'     => array( 'format' => 'string', 'scalar' ),
					'language' => array( 'format' => 'string', 'scalar' ),
					'library'  => array( 'format' => 'string', 'scalar' )
				),
				'optional' => array()
			))
		);
		$this->optional = array();
		$data = $this->validate();
		$this->checkMoment( $data['moment_id'] );

		$snippet = new \Models\Snippet( $data );
		//sets created and modified
		$snippet->validate();

		$result = $this->selectCollection( 'snippets' )->insertOne( $snippet );
		$snippet_id = $result->getInsertedId();
		$this->response->print( array( 'snippet_id' => "{$snippet_id}" ), 201 );
	}

	protected final function PUT(){
		$this->json();
		$this->required = array(
			'snippet_id' => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'moment_id'  => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'config'     => array( 'format' => 'object', 'object' => (object)array(
				'required' => array(),
				'optional' => array(
					'decode'        => array( 'format' => 'boolean', 'scalar' ),
					'method'        => array( 'format' => 'string', 'scalar' ),
					'body_params'   => array( 'format' => 'string', 'default' => array(), 'array' ),
					'header_params' => array( 'format' => 'string', 'default' => array(), 'array' ),
					'query_params'  => array( 'format' => 'string', 'default' => array(), 'array' )
				)
			)),
			'formatter' => array( 'format' => 'object', 'object' => (object)array(
				'required' => array(
					'name'     => array( 'format' => 'string', 'scalar' ),
					'language' => array( 'format' => 'string', 'scalar' ),
					'library'  => array( 'format' => 'string', 'scalar' )
				),
				'optional' => array()
			))
		);
		$this->optional = array();
		$data = $this->validate();
		$this->checkMoment( $data['moment_id'] );

		$snippet = new \Models\Snippet( $data );
		$snippet->validate();
\Log::info( 'validated' );

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
				'modified' => true
			)
		);
		$result = $this->selectCollection( 'snippets' )->updateOne( $query, $update );
		$response = array(
			'snippet_id'     => "{$data['snippet_id']}",
			'is_acknowledged' => $result->isAcknowledged(),
			'matches' => $result->getMatchedCount(),
			'updated' => $result->getModifiedCount()
		);
		
		$this->response->print( $response, 201 );
	}

	private function checkMoment( $moment_id ){
		$momentQuery = array( '_id' => $moment_id );
		$momentResult = $this->selectCollection( 'moments' )->findOne( $momentQuery, array( 'visibility' => 1 ));
		if( $momentResult ){
			if( $momentResult->visibility === 'private' )
				$this->authorize();
		}
		else
			throw new \Exception( "Moment not found: {$moment_id}", 404 );
	}
}
