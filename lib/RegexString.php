<?php

class RegexString{
	public $pattern = null;
	
	public function __construct( $pattern ){
		$this->pattern = $pattern;
	}

	public function test( $text, &$matches=null ){
		return preg_match( $this->pattern, $text, $matches ) > 0;
	}

	public function __toString(){
		return $this->pattern;
	}
}
