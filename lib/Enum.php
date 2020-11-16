<?php
final class Enum extends Format{
	public $converted = false;
	public $options = array();

	public function __construct(){
		parent::__construct( 'enum' );
	}

	public function configure( $attributes ){
		$this->converted = false;
		$this->options = $attributes[ 'enum' ];
		return $this;
	}

	public function format( $self, $val ){
		return $this->converted ? (int)$val : $val;
	}

	public function isValid( $self, $val ){
		if( in_array( $val, $this->options, true ) )
			return true;

		if( ctype_digit( $val ) && in_array( (int)$val, $this->options, true ) ){
			$this->converted = true;
			return true;
		}

		return false;
	}

	public function throwValidationError( $param, $value ){
		$tmp = array();
		foreach( $this->options as $opt ){
			if( $opt === true ){
				$tmp[] = "true";
			}
			else if( $opt === false ){
				$tmp[] = "false";
			}
			else if( is_string( $opt ) ){
				$tmp[] = "'{$opt}'";
			}
			else{
				$tmp[] = $opt;
			}
		}

		$combined = implode( ", ", $tmp );
		throw new IntelePeer_Exception( "The '{$param}' parameter must match one of the following values: {$combined}", 422 );
	}
}
