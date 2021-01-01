<?php

namespace Models;

abstract class MongoModel{
	protected $_id;

	public function __construct( iterable $data=null ){
		if( $data ){
			$this->load( $data );
		}
	}

	public function load( $data ){
		foreach( $data as $key => $val ){
			if( property_exists( $this, $key ) )
				$this->{$key} = $val;
		}
	}

	public abstract function validate();
}
