<?php

namespace Models;

class Plan extends MongoModel{
	//createIndex( $keys = array( "name" => 1 }, $options = array( "name": "name", "sparse": true, "unique" => true ) );

	//BASE:
	//protected $_id;

	public $is_enabled;
	public $name;
	public $projects;
	
	public $moments;
	public $proxy_ratelimit;

	public final function validate(){
		static $required, $optional = array();
		if( empty( $required ) ){
			$required = array(
				'is_enabled' => array( 'format' => 'boolean', 'scalar' ),
				'name'       => array( 'format' => 'string',  'range' => array( 1, 32 ), 'scalar' ),
				'projects'   => array( 'format' => 'integer', 'range' => array( 0 ), 'scalar' ),

				'moments' => array( 'format' => 'object', 'object' => (object)array(
					'optional' => array(),
					'required' => array(
						'history_days' => array( 'format' => 'integer', 'range' => array( 0, 90 ), 'scalar' )
					)
				)),
				'proxy_ratelimit' => array( 'format' => 'object', 'object' => (object)array(
					'optional' => array(),
					'required' => array(
						'init' => array( 'format' => 'integer', 'range' => array( 0 ), 'scalar' ),
						'max'  => array( 'format' => 'integer', 'range' => array( 0 ), 'scalar' ),
						'type' => array( 'format' => 'enum',    'enum'  => array( 'scheduled' ), 'scalar' ),
						'scheduled' => array( 'format' => 'object', 'object' => (object)array(
							'optional' => array(),
							'required' => array(
								'increment' => array( 'format' => 'integer', 'scalar' ),
								'times'     => array( 'format' => 'string',  'array'  )
							)
						))
					)
				))
			);
		}

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return true;
	}
}
