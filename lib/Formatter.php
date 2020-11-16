<?php
abstract class Formatter{
	public $code = 200;
	
	public abstract function emit( &$data, $code=null );
	public abstract function format( &$data );
	public abstract function formatData( &$data );
	public abstract function formatException( Exception &$exception );
}
