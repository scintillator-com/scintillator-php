<?php

namespace Models;

class Plan extends MongoModel{
	//createIndex( $keys = array( "name" => 1 }, $options = array( "name": "name", "sparse": true, "unique" => true ) );

	private $_id;

	public $name;
	public $rate_limit;
	public $projects;

	public final function __construct( iterable $data=null ){
		if( $data ){
			foreach( $data as $key => $val ){
				if( property_exists( $this, $key ) )
					$this->{$key} = $val;
			}
		}
	}

	public final function validate(){
		static $required = array();
		static $optional = array();

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return true;
	}
}
