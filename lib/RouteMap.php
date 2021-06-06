<?php

class RouteMap extends SplObjectStorage{
	private $strings = array();

	public function __construct( iterable $map=null ){
		if( $map ){
			foreach( $map as $k => $v ){
				$this->offsetSet( $k, $v );
			}
		}
	}

	public function contains( $key ){
		return $this->tryGet( $key );
	}

	public function offsetExists( $key ){
		return $this->tryGet( $key );
	}

	public function offsetGet( $key ){
		if( $this->tryGet( $key, $value ) )
			return $value;
	}

	public function offsetSet( $key, $value=null ){
		if( is_scalar( $key ) ){
			$this->strings[ $key ] = $value;
		}
		else if( is_object( $key ) ){
			parent::offsetSet( $key, $value );
		}
		else{
			\Log::info( __METHOD__ );
			throw new \Exception( "Unsupported key type: ". gettype( $key ) );
		}
	}

	public function offsetUnset( $key ){
		if( is_scalar( $key ) ){
			unset( $this->strings[ $key ] );
		}
		else if( is_object( $key ) ){
			parent::offsetUnset( $key );
		}
		else{
			\Log::info( __METHOD__ );
			throw new \Exception( "Unsupported key type: ". gettype( $key ) );
		}
	}

	public function tryGet( $key, &$value=null ){
		//check strings
		if( is_scalar( $key ) ){
			if( array_key_exists( $key, $this->strings ) ){
				$value = $this->strings[ $key ];
				return true;
			}
		}

		//now iterate the non-strings
		$this->rewind();
		while( $this->valid() ){
			if( $this->current()->test( $key ) ){
				$value = $this->getInfo();
				return true;
			}
			else{
				$this->next();
			}
		}

		return false;
	}
}
