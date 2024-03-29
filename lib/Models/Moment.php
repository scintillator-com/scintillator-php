<?php

namespace Models;

class Moment extends MongoModel{
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

	public final static function formatDetail( &$moment ){
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

	public final static function formatSummary( &$moment ){
		$request_created_ms = (int)$moment->request->created->toDateTime()->format( 'Uv' );
		$request = array(
			'created' => $request_created_ms,
			'http_version' => $moment->request->http_version,
			'method' => $moment->request->method,
			'scheme' => $moment->request->scheme,
			'host'   => $moment->request->host,
			'port'   => $moment->request->port,
			'path'   => $moment->request->path,
			'query_string'   => $moment->request->query_string,
			'content_type' => !empty( $moment->request->content_type ) ? $moment->request->content_type : null,
			'content_length' => !empty( $moment->request->content_length ) ? $moment->request->content_length : null,
			'headers' => array(
				'length' => count( $moment->request->headers )
			),
			'query_data' => array(
				'length' => count( $moment->request->query_data )
			)
		);

		$response = null;
		if( !empty( $moment->response->created ) ){
			$response_created_ms = (int)$moment->response->created->toDateTime()->format( 'Uv' );
			$response = array(
				'created'      => $response_created_ms,
				'http_version' => $moment->response->http_version,
				'status_code'  => $moment->response->status_code,
				'content_type' => !empty( $moment->response->content_type ) ? $moment->response->content_type : null,
				'content_length' => !empty( $moment->response->content_length ) ? $moment->response->content_length : null,
				'headers' => array(
					'length' => count( $moment->response->headers )
				)
			);
		}

		return array(
			'moment_id' => "{$moment->_id}",
			'request'   => $request,
			'response'  => $response
		);
	}

	public final static function getSummaryProjection(){
		return array(
			//request
			'request.created' => 1,
			'request.http_version' => 1,
			'request.method' => 1,
			'request.scheme' => 1,
			'request.host'   => 1,
			'request.port'   => 1,
			'request.path'   => 1,
			'request.content_length' => 1,
			'request.content_type' => 1,
			'request.headers' => 1,
			'request.query_data' => 1,
			'request.query_string' => 1,
			//'request.body'

			//response
			'response.created'      => 1,
			'response.http_version' => 1,
			'response.status_code'  => 1,
			'response.content_length' => 1,
			'response.content_type' => 1,
			'response.headers' => 1
			//'response.body'
		);
	}
}
