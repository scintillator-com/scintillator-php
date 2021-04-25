<?php

namespace Models;

class User extends MongoModel{
	//createIndex( $keys = array( "client_key" => 1 }, $options = array( "name": "client_key", "sparse": true, "unique": true ) );
	//createIndex( $keys = array( "email"      => 1 ), $options = array( "name": "email",      "sparse": true, "unique": true ) );
	//createIndex( $keys = array( "username"   => 1 ), $options = array( "name": "username",   "sparse": true, "unique": true ) );

	//BASE:
	//protected $_id;

	public $algorithm;
	public $client_key;
	public $created;
	public $created_by;
	public $email;
	public $is_enabled;
	public $is_verified;
	public $first_name;
	public $hash;
	public $last_login;
	public $last_name;
	public $login_history;
	public $modified;
	public $org_id;
	public $username;

	public final function __construct( iterable $data=null ){
		if( $data ){
			foreach( $data as $key => $val ){
				if( property_exists( $this, $key ) )
					$this->{$key} = $val;
			}
		}
	}

	public final function getID(){
		return $this->_id;
	}

	public final function setID( \MongoDB\InsertOneResult $result ){
		$this->_id = $result->getInsertedId();
	}

	public final static function onLogin( $user ){
		$now = new \MongoDB\BSON\UTCDateTime();
		$update = array(
			'$push' => array(
				'login_history' => array(
					'$each'     => array( $now ),
					'$position' => 0,
					'$slice'    => 3
				)
			),
			'$set'  => array(
				'last_login'    => $now
			)
		);

		return $update;
	}

	public final function validate(){
		static $required = array(
			'algorithm'  => array( 'format' => 'enum', 'enum' => array( 'argon2id' ), 'scalar' ),
			'client_key' => array( 'format' => 'base64', 'scalar' ),
			'created'    => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
			'email'      => array( 'format' => 'string', 'length' => array( 6, 255 ), 'scalar' ),
			'is_enabled' => array( 'format' => 'boolean', 'scalar' ),
			'is_verified' => array( 'format' => 'boolean', 'scalar' ),
			'first_name' => array( 'format' => 'string', 'length' => array( 1, 32 ), 'scalar' ),
			'hash'       => array( 'format' => 'string', 'scalar' ),
			'last_name'  => array( 'format' => 'string', 'length' => array( 1, 32 ), 'scalar' ),
			'login_history' => array( 'format' => 'MongoDB::UTCDateTime', 'array' ),
			'modified'   => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
			'username'   => array( 'format' => 'string', 'length' => array( 6, 255 ), 'scalar' ),
		);

		static $optional = array(
			'_id'        => array( 'format' => 'MongoDB::ObjectId',    'scalar' ),
			'created_by' => array( 'format' => 'MongoDB::ObjectId',    'scalar' ),
			'last_login' => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
			'org_id'     => array( 'format' => 'MongoDB::ObjectId',    'scalar' )
		);

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return $this;
	}
}
