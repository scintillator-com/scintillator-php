<?php

class RouteMap extends SplObjectStorage{
	private $strings = array();

	public function __construct( iterable $map=null ){
		if( $map ){
			foreach( $map as $key => $value ){
				$this->offsetSet( $key, $value );
			}
		}
	}

	public function contains( $search ){
		return $this->tryGet( $search );
	}

	public function offsetExists( $search ){
		return $this->tryGet( $search );
	}

	public function offsetGet( $search ){
		if( $this->tryGet( $search, $value ) )
			return $value;
	}

	public function offsetSet( $search, $value=null ){
		if( is_scalar( $search ) ){
			$this->strings[ $search ] = $value;
		}
		else if( is_object( $search ) ){
			parent::offsetSet( $search, $value );
		}
		else{
			\Log::info( __METHOD__ );
			throw new \Exception( "Unsupported search type: ". gettype( $search ) );
		}
	}

	public function offsetUnset( $search ){
		if( is_scalar( $search ) ){
			unset( $this->strings[ $search ] );
		}
		else if( is_object( $search ) ){
			parent::offsetUnset( $search );
		}
		else{
			\Log::info( __METHOD__ );
			throw new \Exception( "Unsupported search type: ". gettype( $search ) );
		}
	}

	public function tryGet( $search, &$value=null, &$key=null ){
		//check strings
		if( is_scalar( $search ) ){
			if( array_key_exists( $search, $this->strings ) ){
				$key = $search;
				$value = $this->strings[ $search ];
				return true;
			}
		}

		//now iterate the non-strings
		$this->rewind();
		while( $this->valid() ){
			if( $this->current()->test( $search ) ){
				$key = $this->current();
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
