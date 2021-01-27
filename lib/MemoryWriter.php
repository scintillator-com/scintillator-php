<?php
class MemoryWriter{
	private $fp = null;
	
	private $nextPrefix = '';
	private $prefix = '';

	//private $suffix = '';
	private $tokens = array();

	public function __construct(){
		$this->fp = fopen( 'php://memory', 'w+' );
	}

	public function indent( $str="\t" ){
		$this->tokens[] = $str;
		$this->nextPrefix = $this->prefix = implode( $this->tokens );
		return $this;
	}

	public function prefix( $prefix ){
		$this->prefix = $prefix;
		return $this;
	}

	/*
	public function suffix( $suffix ){
		$this->suffix = $suffix;
		return $this;
	}
	*/

	public function outdent(){
		array_pop( $this->tokens );
		$this->nextPrefix = $this->prefix = implode( $this->tokens );
		return $this;
	}

	public function write( $str ){
		fwrite( $this->fp, "{$this->nextPrefix}{$str}" );

		if( $this->nextPrefix )
			$this->nextPrefix = '';

		return $this;
	}

	public function writeLine( $str='' ){
		if( $str )
			fwrite( $this->fp, "{$this->nextPrefix}{$str}". PHP_EOL );
		else
			fwrite( $this->fp, PHP_EOL );

		$this->nextPrefix = $this->prefix;
		return $this;
	}

	public function __toString(){
		$pos = ftell( $this->fp );
		fseek( $this->fp, 0 );
		return fread( $this->fp, $pos );
	}
}