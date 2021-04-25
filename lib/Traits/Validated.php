<?php

namespace Traits;

trait Validated{
	protected $optional = array();
	protected $pageable = null;
	protected $required = array();

	protected final function pageable( $default, $max ){
		$this->pageable = true; //compact( 'default', 'max' );

		$this->optional[ 'page'     ] = array( 'format' => 'integer', 'default' => 1,        'range' => array( 1, PHP_INT_MAX ), 'scalar' );
		$this->optional[ 'pageSize' ] = array( 'format' => 'integer', 'default' => $default, 'range' => array( 1, $max ), 'scalar' );
		return $this;
	}

	/*
	* Function validate
	* Validate parameters given
	* @param (array) $requestParameters - parameters given
	*/
	protected final function validate( $requestData = null ){
		$data = isset( $requestData ) ? $requestData : $this->request->data;
		return \Validator::validate( $data, $this->required, $this->optional );
	}
}