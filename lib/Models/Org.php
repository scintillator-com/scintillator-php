<?php

namespace Models;

class Org extends MongoModel{
	//createIndex( $keys = array( "name"       => 1 ), $options = array( "name": "name",       "sparse": true, "unique": true ) );
	//createIndex( $keys = array( "client_key" => 1 }, $options = array( "name": "client_key", "sparse": true, "unique": true ) );

	//BASE:
	//protected $_id;

	public $admins;
	public $client_key;
	public $created;
	public $created_by;
	public $enabled;
	public $modified;
	public $name;
	public $plan;
	public $users;

	public final function getID(){
		return $this->_id;
	}

	public final function setID( \MongoDB\InsertOneResult $result ){
		$this->_id = $result->getInsertedId();
	}

	public final function validate(){
		static $required, $optional;
		if( empty( $required ) ){
			$required = array(
				'client_key' => array( 'format' => 'base64', 'scalar' ),
				'created'    => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
				'created_by' => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
				'enabled'    => array( 'format' => 'boolean', 'scalar' ),
				'modified'   => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
				'name'       => array( 'format' => 'string', 'length' => array( 1, 255 ), 'scalar' ),
				'plan'       => array( 'format' => 'object', 'object' => (object)array(
					'optional' => array(),
					'required' => array(
						'_id'  => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
						'name' => array( 'format' => 'string', 'scalar' )
					)
				))
			);
		}

		if( empty( $optional ) ){
			$optional = array(
				'_id'    => array( 'format' => 'MongoDB::ObjectId', 'default' => null, 'scalar' ),
				'admins' => array( 'format' => 'MongoDB::ObjectId', 'default' => array(), 'array' ),
				'users'  => array( 'format' => 'MongoDB::ObjectId', 'default' => array(), 'array' )
			);
		}

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return $this;
	}
}

