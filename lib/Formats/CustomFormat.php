<?php

namespace Formats;

final class CustomFormat extends \Format{
	public $formatter = null;
	public $validator = null;
	public $options = array();

	public function __construct(){
		parent::__construct( 'custom format' );
	}

	public function configure( $attributes ){
		$this->formatter = $attributes[ 'formatter' ];
		$this->validator = $attributes[ 'validator' ];
		return $this;
	}

	public function format( $self, $val ){
		return $this->formatter( $val );
	}

	public function isValid( $self, $val ){
		return $this->validator( $val );
	}

	public function throwValidationError( $param, $value ){
		throw new \Exception( "The '{$param}' parameter must be valid", 422 );
	}
}
