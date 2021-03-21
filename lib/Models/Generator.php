<?php

namespace Models;

class Generator extends MongoModel{
	//BASE:
	//protected $_id;

	public $language;
	public $library;
	public $platforms;
	public $versions;

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
				'language'  => array( 'format' => 'string', 'scalar' ),
				'library'   => array( 'format' => 'string', 'scalar' ),
				'platforms' => array( 'format' => 'string', 'array'  ),
				'versions'  => array( 'format' => 'string', 'array'  )
			);
		}

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return $this;
	}
}

