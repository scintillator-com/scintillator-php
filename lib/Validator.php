<?php

class Validator{
	private static $depth = 0;
	public $optional = array();
	public $required = array();

	public function __construct( $required, $optional ){
		$this->optional = $optional;
		$this->required = $required;
	}

	public static function validate( $data, $required, $optional, $ns = '' ){
		self::$depth++;

		$validator = new \Validator( $required, $optional );
		$validator->_validateAny( $data );
		$validator->_validateExclusive( $data );

		$reducedData = array();
		
		//validate require parameters
		$validator->_validate( $reducedData, $data, $validator->required, true, $ns );

		//validate optional parameters
		$validator->_validate( $reducedData, $data, $validator->optional, false, $ns );

		self::$depth--;
		return $reducedData;
	}

	private function _validate( &$reducedData, $data, $params, $required, $ns ){
		if( empty( $params ) )
			return;

		foreach( $params as $param => $attributes ){
			$fqpm = $ns ? "{$ns}.{$param}" : "{$param}";
			if( !isset( $data[ $param ] ) ){
				if( $required ){
					throw new \Exception( "The '{$param}' parameter is required.", 422 );
				}else if( array_key_exists( "default", $attributes ) ){
					$reducedData[ $param ] = $attributes[ "default" ];
				}
				continue;
			}

			//validate parameter dependencies
			if( !empty( $attributes[ 'requires' ] ) ){
				foreach( (array)$attributes[ 'requires' ] as $req ){
					if( !isset( $data[ $req ] ) )
						throw new \Exception( "When using the '{$param}' parameter, the '{$req}' parameter is also required.", 422 );
				}
			}

			$value = $data[ $param ];
			$format = \Format::get( $attributes );
			if( is_numeric_array( $value ) ){
				if( in_array( 'scalar', $attributes, true ) )
					throw new \Exception( "The '{$param}' parameter must be scalar.", 422 );

				if( !$format->isValidArray( $value ) )
					$format->throwValidationError( $param, $value );

				$formatted = $format->formatArray( $value );
				if( !empty( $attributes[ 'length' ] ) )
					$this->_validateLength( $param, $formatted, $attributes[ 'length' ] );

				if( !empty( $attributes[ 'range' ] ) )
					$this->_validateRange( $param, $formatted, $attributes[ 'range' ] );

				$reducedData[ $param ] = $formatted;
			}
			else{
				if( in_array( 'array', $attributes, true ) )
					throw new \Exception( "The '{$param}' parameter must be an array.", 422 );

				if( !$format->isValidScalar( $value ) )
					$format->throwValidationError( $param, $value );

				$formatted = $format->formatScalar( $value );
				if( !empty( $attributes[ 'length' ] ) )
					$this->_validateLength( $param, (array)$formatted, $attributes[ 'length' ] );

				if( !empty( $attributes[ 'range' ] ) )
					$this->_validateRange( $param, (array)$formatted, $attributes[ 'range' ] );

				$reducedData[ $param ] = $formatted;
			}
		}
	}

	private function _validateAny( $data ){
		$any = array();
		foreach( $this->required as $param => $attributes ){
			if( in_array( 'any', $attributes, true ) ){
				$any[] = $param;
			}
		}

		foreach( $this->optional as $param => $attributes ){
			if( in_array( 'any', $attributes, true ) ){
				$any[] = $param;
			}
		}

		if( empty( $any ) )
			return;
		
		$found = array();
		foreach( $any as $param ){
			if( isset( $data[ $param ] ) )
				return;
		}

		sort( $any );
		$combined = "'". implode( "', '", $any ) ."'";
		throw new \Exception( "At least one of the following parameters is required: {$combined}", 422 );
	}

	private function _validateExclusive( $data ){
		foreach( $this->optional as $param => $attributes ){
			if( !isset( $data[ $param ] ) )
				continue;

			if( !isset( $attributes[ 'exclusive' ] ) )
				continue;

			foreach( (array)$attributes[ 'exclusive' ] as $ex ){
				if( isset( $data[ $ex ] ) )
					throw new \Exception( "The following parameters are mutually exclusive: '{$param}', '{$ex}'", 409 );
			}
		}
	}

	private function _validateLength( $param, $values, $range ){
		list( $min, $max ) = $range;
		foreach( $values as $v ){
			if( strlen( $v ) < $min ){
				if( $max == $min )
					throw new \Exception( "The '{$param}' parameter must have length {$min}.", 422 );

				if( $max == PHP_INT_MAX )
					throw new \Exception( "The '{$param}' parameter must have length {$min} or greater.", 422 );

				throw new \Exception( "The '{$param}' parameter must have length between {$min} and {$max}.", 422 );
			}

			if( strlen( $v ) > $max )
				throw new \Exception( "The '{$param}' parameter must have length between {$min} and {$max}.", 422 );
		}
	}

	private function _validateRange( $param, $values, $range ){
		list( $min, $max ) = $range;
		foreach( $values as &$v ){
			if( $v < $min ){
				if( $max == PHP_INT_MAX )
					throw new \Exception( "The '{$param}' parameter must be greater than or equal to {$min}.", 422 );
				else
					throw new \Exception( "The '{$param}' parameter must be between {$min} and {$max}.", 422 );
			}

			if( $v > $max )
				throw new \Exception( "The '{$param}' parameter must be between {$min} and {$max}.", 422 );
		}
	}
}
