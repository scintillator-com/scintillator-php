<?php
class Format{
	private static $formats = array();

	public $attributes = array();
	public $format = null;
	public $isValid = null;
	public $name = null;

	public function __construct( $name ){
		$this->name = $name;
	}

	public function __call( $name, $args ){
		if( !isset( $this->{$name} ) )
			throw new \Exception( "Format member is not defined: {$this->name}->{$name}." );

		if( !is_callable( $this->{$name} ) )
			throw new \Exception( "Format member is not callable: {$this->name}->{$name}." );

		return call_user_func_array( $this->{$name}, $args);
	}

	public function configure( $attributes ){
		$this->attributes = $attributes;
		return $this;
	}

	public function formatArray( $values ){
		$newValues = array();
		foreach( $values as &$v ){
			$newValues[] = $this->formatScalar( $v );
		}
		return $newValues;
	}

	public function formatScalar( $value ){
		return $this->format( $this, $value );
	}

	public function isValidArray( $values ){
		foreach( $values as &$v ){
			if( !$this->isValidScalar( $v ) )
				return false;
		}
		return true;
	}

	public function isValidScalar( $value ){
		return $this->isValid( $this, $value );
	}

	public function throwValidationError( $param, $value ){
		if( $this->name == "string" )
			throw new \Exception( "The '{$param}' parameter must be formatted as a non-empty {$this->name}.", 422 );
		else
			throw new \Exception( "The '{$param}' parameter must be formatted as a(n) {$this->name}.", 422 );
	}

  	public static function get( $attributes ){
		if( empty( self::$formats ) ){
			self::load();
		}

		$name = $attributes[ "format" ];
		if( $name === 'object' ){
			$obj = new \Formats\ObjectFormat();
			return $obj->configure( $attributes );
		}
		else if( isset( self::$formats[ $name ] ) ){
			return self::$formats[ $name ]->configure( $attributes );
		}
		else{
			throw new \Exception( "Format not defined: {$name}.", 500 );
		}
  	}

	private static function load(){
		//TODO: https://www.php.net/manual/en/function.filter-var.php
		
		self::$formats[ 'alphanumeric' ] = new \Format( 'alphanumeric' );
		self::$formats[ 'alphanumeric' ]->format = function( $self, $val ){
			return preg_replace( '/[[:^alnum:]]/', '', $val );
		};
		self::$formats[ 'alphanumeric' ]->isValid = function( $self, $val ){
			return ctype_alnum( $val );
		};

		self::$formats[ 'array' ] = new \Format( 'array' );
		self::$formats[ 'array' ]->format = function( $self, $val ){
			return (array)$val;
		};
		self::$formats[ 'array' ]->isValid = function( $self, $val ){
			return (is_array( $val) && !empty($val));
		};


		self::$formats[ 'base64' ] = new \Format( 'base64' );
		self::$formats[ 'base64' ]->format = function( $self, $val ){
			return "{$val}";
		};
		self::$formats[ 'base64' ]->isValid = function( $self, $val ){
			return preg_match( '/^[[:alnum:]\+\/]+$/', $val );
		};



		self::$formats[ 'boolean' ] = new \Format( 'boolean' );
		self::$formats[ 'boolean' ]->format = function( $self, $val ){
			if( is_bool( $val ) )
				return $val;

			return $val === "true";
		};
		self::$formats[ 'boolean' ]->isValid = function( $self, $val ){
			return $val === "true" || $val === "false" || is_bool( $val );
		};

		self::$formats[ 'custom' ] = new \Formats\CustomFormat();

		//self::$formats[ 'email' ] = new \Email();

		self::$formats[ 'enum' ] = new \Formats\Enum();


		self::$formats[ 'hex' ] = new \Format( 'hexadecimal' );
		self::$formats[ 'hex' ]->format = function( $self, $val ){
			return "{$val}";
		};
		self::$formats[ 'hex' ]->isValid = function( $self, $val ){
			return ctype_xdigit( $val );
		};

		self::$formats[ 'integer' ] = new \Format( 'integer' );
		self::$formats[ 'integer' ]->format = function( $self, $val ){
			return (int)$val;
		};
		self::$formats[ 'integer' ]->isValid = function( $self, $val ){
			//\Log::info( $val );
			//return is_int( filter_var( $val, FILTER_VALIDATE_INT ) );

			//$start = hrtime( true );
			$res = preg_match( '/^((0)|(-?[1-9]\d*))$/', $val );
			//$res = is_int( filter_var( $val, FILTER_VALIDATE_INT ) );
			//\Log::info( 'Duration: '. ( hrtime( true ) - $start ) );
			return $res;
		};

		self::$formats[ 'iso8601' ] = new \Formats\ISO8601();

		self::$formats[ 'numeric' ] = new \Format( 'numeric' );
		self::$formats[ 'numeric' ]->format = function( $self, $val ){
			return preg_replace( '/\D/', '', "{$val}" );
		};
		self::$formats[ 'numeric' ]->isValid = function( $self, $val ){
			return is_numeric( $val ); //ctype_digit( "{$val}" );
		};

		self::$formats[ 'object' ] = new \Formats\ObjectFormat();

		self::$formats[ 'string' ] = new \Format( 'string' );
		self::$formats[ 'string' ]->format = function( $self, $val ){
			return trim( preg_replace( "/\s+/", " ", "{$val}" ) );
		};
		self::$formats[ 'string' ]->isValid = function( $self, $val ){
			$tmp = trim( "{$val}" );
			if( array_key_exists( 'default', $self->attributes ) ){
				if( $tmp === $self->attributes[ 'default' ] ){
					return true;
				}
			}

			return strlen( $tmp ) > 0;
		};

		self::$formats[ 'url' ] = new \Format( 'string' );
		self::$formats[ 'url' ]->format = function( $self, $val ){
			return "{$val}";
		};
		self::$formats[ 'url' ]->isValid = function( $self, $val ){
			$parsed = parse_url( $val );
			return $parsed !== false;
		};




		self::$formats[ 'MongoDB::ObjectId' ] = new \Format( 'MongoDB::ObjectId' );
		self::$formats[ 'MongoDB::ObjectId' ]->format = function( $self, $val ){
			if( $val instanceof MongoDB\BSON\ObjectId )
				return $val;
			else
				return new \MongoDB\BSON\ObjectId( $val );
		};
		self::$formats[ 'MongoDB::ObjectId' ]->isValid = function( $self, $val ){
			if( $val instanceof MongoDB\BSON\ObjectId ){
				return true;
			}
			else{
				try{
					new \MongoDB\BSON\ObjectId( $val );
					return true;
				}
				catch( \Exception $_ ){
					return false;
				}
			}
		};

		self::$formats[ 'MongoDB::UTCDateTime' ] = new \Format( 'MongoDB::UTCDateTime' );
		self::$formats[ 'MongoDB::UTCDateTime' ]->format = function( $self, $val ){
			if( $val instanceof MongoDB\BSON\UTCDateTime )
				return $val;
			else
				return new \MongoDB\BSON\UTCDateTime( $val );
		};
		self::$formats[ 'MongoDB::UTCDateTime' ]->isValid = function( $self, $val ){
			if( $val instanceof MongoDB\BSON\UTCDateTime ){
				return true;
			}
			else{
				try{
					new \MongoDB\BSON\UTCDateTime( $val );
					return true;
				}
				catch( \Exception $_ ){
					return false;
				}
			}
		};
  	}
}
