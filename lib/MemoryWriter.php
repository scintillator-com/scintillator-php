<?php
class MemoryWriter{
	private $fp = null;
	private $prefix = '';
	private $suffix = '';
	private $tokens = array();

	public function __construct(){
		$this->fp = fopen( 'php://memory', 'w+' );
	}

	public function indent( $str="\t" ){
		$this->tokens[] = $str;
		$this->prefix = implode( $this->tokens );
		return $this;
	}

	public function prefix( $prefix ){
		$this->prefix = $prefix;
		return $this;
	}

	public function suffix( $suffix ){
		$this->suffix = $suffix;
		return $this;
	}

	public function outdent(){
		array_pop( $this->tokens );
		$this->prefix = implode( $this->tokens );
		return $this;
	}

	public function write( $str ){
		fwrite( $this->fp, "{$this->prefix}{$str}{$this->suffix}" );
		return $this;
	}

	public function writeLine( $str='' ){
		if( $str )
			fwrite( $this->fp, "{$this->prefix}{$str}{$this->suffix}". PHP_EOL );
		else
			fwrite( $this->fp, PHP_EOL );

		return $this;
	}

	public function __toString(){
		$pos = ftell( $this->fp );
		fseek( $this->fp, 0 );
		return fread( $this->fp, $pos );
	}
}