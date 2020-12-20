<?php

namespace Models;

class Snippet extends MongoModel{
	//createIndex( $keys = array( "moment_id" => 1 }, $options = array( "name": "moment_id", "sparse": true ) );

	private $_id;

	public $config;
	public $created;
	public $formatter;
	public $modified;
	public $moment_id;
	public $views;
	public $likes;

	public final function __construct( iterable $data=null ){
		if( $data ){
			foreach( $data as $key => $val ){
				if( property_exists( $this, $key ) )
					$this->{$key} = $val;
			}
		}
	}

	public final function validate(){
		static $required;
		if( empty( $required ) ){
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
		}

		static $optional = array(
			'created'  => array( 'format' => 'MongoDB::UTCDateTime', 'default' => new \MongoDB\BSON\UTCDateTime() ),
			'modified' => array( 'format' => 'MongoDB::UTCDateTime', 'default' => new \MongoDB\BSON\UTCDateTime() )
		);

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return true;
	}
}
