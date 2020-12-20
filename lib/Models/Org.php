<?php

namespace Models;

class Org extends MongoModel{
	//createIndex( $keys = array( "name"       => 1 ), $options = array( "name": "name",       "sparse": true, "unique": true ) );
	//createIndex( $keys = array( "client_key" => 1 }, $options = array( "name": "client_key", "sparse": true, "unique": true ) );

	private $_id;
	public $admins;
	public $client_key;
	public $created;
	public $created_by;
	public $enabled;
	public $modified;
	public $name;
	public $plan;
	public $users;

	public final function __construct( iterable $data=null ){
		if( $data ){
			foreach( $data as $key => $val ){
				if( property_exists( $this, $key ) )
					$this->{$key} = $val;
			}
		}
	}

	public final function validate(){
		static $required = array(
			'client_key' => array( 'format' => 'hex', 'scalar' ),
			'created'    => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
			'created_by' => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'enabled'    => array( 'format' => 'boolean', 'scalar' ),
			'modified'   => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
			'name'       => array( 'format' => 'string', 'length' => array( 1, 255 ), 'scalar' ),
			'plan'       => array( 'format' => 'enum', 'enum' => array( 'free', 'basic' ), 'scalar' )
		);

		static $optional = array(
			'_id'    => array( 'format' => 'MongoDB::ObjectId', 'default' => null, 'scalar' ),
			'admins' => array( 'format' => 'MongoDB::ObjectId', 'default' => array(), 'array' ),
			'users'  => array( 'format' => 'MongoDB::ObjectId', 'default' => array(), 'array' )
		);

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return $this;
	}
}

