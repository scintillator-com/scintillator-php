<?php

class HTTP_StringBody extends HTTP_Body{
	private $_body = '';

	public function __construct( &$stringBody ){
		$this->_body = $stringBody;
	}

	public function asString(){
		return $this->_body;
	}

	public function serialize( &$contentType = null ){
		if( $contentType === HTTP_Data::APPLICATION_JSON )
			return json_decode( $this->_body, true );
		else if( $contentType === HTTP_Data::FORM_URL_ENCODED )
			return $this->_parseQuery( $this->_body );
		else
			return $this->_body;
	}

	public function __toString(){
		return $this->_body;
	}

	private function _parseQuery( $query ){
		$data = array();
		$pairs = explode('&', $query);
		foreach( $pairs as &$pair ){
			list( $key, $value ) = explode( '=', $pair, 2 );
			$data[] = array(
				'key'   => $key,
				'value' => $value,
				'index' => count( $data )
			);
		}

		return $data;
	}
}

