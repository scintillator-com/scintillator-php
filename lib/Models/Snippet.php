<?php

namespace Models;

class Snippet extends MongoModel{
	//createIndex( $keys = array( "moment_id" => 1 }, $options = array( "name": "moment_id", "sparse": true ) );

	//BASE:
	//protected $_id;

	public $config;
	public $created;
	public $formatter;
	public $modified;
	public $moment_id;
	public $views = 0;
	public $likes = 0;

	public final function __construct( iterable $data=null ){
		if( $data ){
			foreach( $data as $key => $val ){
				if( property_exists( $this, $key ) )
					$this->{$key} = $val;
			}
		}

		if( empty( $data['created'] ) ){
			$now = new \MongoDB\BSON\UTCDateTime();
			$this->created  = $now;
			$this->modified = $now;
		}
	}


	public final function validate(){
		static $required, $optional;
		if( empty( $required ) ){
			$required = array(
				'moment_id' => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
				'config'    => array( 'format' => 'object', 'object' => (object)array(
					'required' => array(),
					'optional' => array(
						"method"        => array( 'format' => 'string', 'scalar' ),
						"decode"        => array( 'format' => 'boolean', 'scalar' ),
						"body_params"   => array( 'format' => 'string', 'default' => array(), 'array' ),
						"header_params" => array( 'format' => 'string', 'default' => array(), 'array' ),
						"query_params"  => array( 'format' => 'string', 'default' => array(), 'array' )
					)
				)),
				'formatter' => array( 'format' => 'object', 'object' => (object)array(
					'required' => array(
						"name"     => array( 'format' => 'string', 'scalar' ),
						"language" => array( 'format' => 'string', 'scalar' ),
						"library"  => array( 'format' => 'string', 'scalar' )
					),
					'optional' => array()
				))
			);
		}

		$now = new \MongoDB\BSON\UTCDateTime();
		if( empty( $optional ) ){
			$optional = array(
				'created'  => array( 'format' => 'MongoDB::UTCDateTime', 'default' => $now, 'scalar' ),
				'modified' => array( 'format' => 'MongoDB::UTCDateTime', 'default' => $now, 'scalar' )
			);
		}

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		//if( empty( $data['created'] ) ){
		//	$this->['created'] = $now;
		//	$data['modified'] = $now;
		//}

		return true;
	}
}
