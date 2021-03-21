<?php

namespace Models;

class Project extends MongoModel{
	//db.projects.createIndex({ "org_id" : 1, "host" : 1 }, { "name": "org_host", "unique": true })

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
