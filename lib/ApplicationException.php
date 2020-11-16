<?php
class ApplicationException extends Exception{
	public $code;
	public $message;
	
	public function __construct( $message, $code = 0, Exception &$previous=null ){
		$this->message = $message;
		$this->code = $code;

		parent::__construct( $message, $code, $previous );
	}

	public function set( $key, &$value ){
		if( $value ){
			$this->{$key} = $value;
		}
	}

	public function equals( ApplicationException $ex ){
		if( $this->message != $ex->message )
			return false;

		if( $this->code != $ex->code )
			return false;

		return true;
	}

	public function __toString(){
		$logStr = sprintf( "%s - %s:%s from %s:%d", get_class(), $this->code, $this->message, $this->file, $this->line );
		if( !empty( $this->details ) ){
			$logStr .= PHP_EOL ."\t". $this->details;
		}
		return $logStr;
	}
}
