<?php

namespace Models;

class Cooldown extends MongoModel{
	//BASE:
	//protected $_id;

	public $org_id;
	public $key;
	public $created;
	public $expires;
	public $hits;
	public $is_active;


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
				'org_id'    => array( 'format' => 'MongoDB::ObjectId',    'scalar' ),
				'key'       => array( 'format' => 'string',               'scalar' ),
				'created'   => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
				'hits'      => array( 'format' => 'integer',              'scalar' ),
				'is_active' => array( 'format' => 'boolean',              'scalar' )
			);
		}

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return $this;
	}
}

