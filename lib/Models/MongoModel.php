<?php

namespace Models;

abstract class MongoModel{
	public static function load( $data ){
		throw new Exception( 'Not Implemented' );
	}

	public abstract function validate();
}
