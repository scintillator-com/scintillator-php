<?php

namespace Models;

class Alert extends MongoModel{
	//BASE:
	//protected $_id;

	//filters?

	public $org_id;
	public $created;
	public $created_by;
	public $is_enabled;
	public $modified;
	public $modified_by;
	public $email;
	public $sms;

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
				'org_id'     => array( 'format' => 'MongoDB::ObjectId',    'scalar' ),
				'created'    => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
				'created_by' => array( 'format' => 'MongoDB::ObjectId',    'scalar' ),
				'is_enabled' => array( 'format' => 'boolean',              'scalar' ),
				'modified'   => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
				'modified_by' => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
				'cooldown'   => array( 'format' => 'object', 'object' => (object)array(
					'required' => array(
						'seconds'    => array( 'format' => 'integer', 'scalar' ),
						'attributes' => array( 'format' => 'string',  'array' )
					)
				)),
				'email' => array( 'format' => 'string', 'array' ),
				'sms'   => array( 'format' => 'string', 'array' )
			);
		}

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return $this;
	}
}

