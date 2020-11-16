<?php

class ObjectFormat extends Format{
	public $optional  = array();
	public $required  = array();

	public function __construct(){
		parent::__construct( 'object' );
	}

	public function configure( $attributes ){
		if( !empty( $attributes[ 'object' ]->optional ) ){
			$this->optional = $attributes[ 'object' ]->optional;
		}
		
		if( !empty( $attributes[ 'object' ]->required ) ){
			$this->required = $attributes[ 'object' ]->required;
		}

		return $this;
	}

	public function formatArray( $values ){
		$formatted = array();
		foreach( $values as $value ){
			$formatted[] = $this->formatScalar( $value );
		}
		return $formatted;
	}

	public function formatScalar( $value ){
		$formatted = array();
		foreach( $this->required as $param => $attributes ){
			$data = $value[ $param ];
			$format = Format::get( $attributes );
			if( is_numeric_array( $data ) ){
				$formatted[ $param ] = $format->formatArray( $data );
			}
			else{
				$formatted[ $param ] = $format->formatScalar( $data );
			}
		}

		foreach( $this->optional as $param => $attributes ){
			if( array_key_exists( $param, $value ) ){
				$data = $value[ $param ];
				$format = Format::get( $attributes );
				if( is_numeric_array( $data ) ){
					$formatted[ $param ] = $format->formatArray( $data );
				}
				else{
					$formatted[ $param ] = $format->formatScalar( $data );
				}
			}
			else if( array_key_exists( 'default', $attributes ) ){
				$formatted[ $param ] = $attributes[ 'default' ];
			}
			else{
				continue;
			}
		}

		return $formatted;
	}

	public function isValidScalar( $value ){
		try{
			RequestValidator::validate( $value, $this->required, $this->optional );
			return true;
		}
		catch( Exception $ex ){
			return false;
		}
	}

	public function isValidArray( $values ){
		if( is_numeric_array( $values ) ){
			foreach( $values as $value ){
				if( !$this->isValidScalar( $value ) )
					return false;
			}
		}
		else{
			RequestValidator::validate( $values, $this->required, $this->optional );
		}

		return true;
	}
	
	public function throwValidationError( $param, $value ){
		RequestValidator::validate( $value, $this->required, $this->optional, "{$param}" );
	}
}
