<?php

namespace Models;

class Project extends MongoModel{
	//createIndex( $keys = array( "request.created"        => 1 }, $options = array( "name": "request.created", "sparse": true ) );
	//createIndex( $keys = array( "request.method"         => 1 }, $options = array( "name": "request.method",  "sparse": true ) );
	//createIndex( $keys = array( "request.scheme"         => 1 }, $options = array( "name": "request.scheme",  "sparse": true ) );
	//createIndex( $keys = array( "request.host"           => 1 }, $options = array( "name": "request.host",    "sparse": true ) );
	//createIndex( $keys = array( "request.path"           => 1 }, $options = array( "name": "request.path",    "sparse": true ) );
	//createIndex( $keys = array( "request.content_type"   => 1 }, $options = array( "name": "request.content_type", "sparse": true ) );
	//createIndex( $keys = array( "request.content_length" => 1 }, $options = array( "name": "request.content_length", "sparse": true ) );

	//createIndex( $keys = array( "response.content_type"   => 1 }, $options = array( "name": "response.content_type", "sparse": true ) );
	//createIndex( $keys = array( "response.content_length" => 1 }, $options = array( "name": "response.content_length", "sparse": true ) );
	//createIndex( $keys = array( "response.status_code"    => 1 }, $options = array( "name": "response.status_code",  "sparse": true ) );

	//BASE:
	//protected $_id;

	public final function validate(){
		return true;
	}

	public final static function formatDetail( &$project ){
		throw new Exception( 'Not implemented', 501 );

		$request = $moment->request;
		$request->created = (int)$moment->request->created->toDateTime()->format( 'Uv' );

		$response = null;
		if( !empty( $moment->response ) ){
			$response = $moment->response;
			$response->created = (int)$moment->response->created->toDateTime()->format( 'Uv' );
		}

		return array(
			'moment_id' => "{$moment->_id}",
			'request'   => $request,
			'response'  => $response
		);
	}

	public final static function formatSummary( &$project ){
		$created_ms = (int)$project->created->toDateTime()->format( 'Uv' );
		$summary = array(
			'created'   => $created_ms,
			'host'      => $project->host,
			'is_locked' => $project->is_locked,
			'moments'   => $project->moments
		);
		return $summary;
	}
}
