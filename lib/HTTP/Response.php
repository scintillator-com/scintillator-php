<?php

class HTTP_Response extends HTTP_Data{
	private $http_version = null;
	private $status_code = null;
	private $_statusText = null;

	public static function loadStream( &$stream ){
		$stats = fstat( $stream );
		$initPos = ftell( $stream );
		fseek( $stream, 0 );

		$response = new HTTP_Response();
		if( !feof( $stream ) ){
			$line = trim( fgets( $stream ) );
			Log::info( $line );
			$pieces = preg_split( '/\s+/', $line, 3 );
			list( $response->http_version, $response->status_code, $response->_statusText ) = $pieces;
			$response->status_code = (int)$response->status_code;
		}

		$headers = self::readHeaderStream( $stream );
		$response->loadHeaders( $headers );

		if( $stats['size'] < 1000000 ){
			$response->loadBody( $stream );
		}

		//TODO: before we read the body, what is the content type?
		//$response->_bodyPos = ftell( $stream );
		//if( $stats['size'] < 1000000 ){
		//	$response->body = fread( $stream, $stats['size'] - $response->_bodyPos );
		//}

		fseek( $stream, $initPos );
		return $response;
	}

	public function getParsedData(){
		throw new Exception( 'Not implemented' );
	}

	public function getStatusCode(){
		return $this->status_code;
	}

	public function getStatusHeader(){
		return "{$this->http_version} {$this->status_code} {$this->_statusText}";
	}

	public function serialize(){
		$content_type = $this->getContentType();

		$data = array(
			'created' => new \MongoDB\BSON\UTCDateTime(),
			'http_version' => $this->http_version,
			'content_type' => $content_type,
			'headers' => array(),
			'body'    => null,
		);

		if( !empty( $this->headers ) ){
			foreach( $this->headers as &$header ){
				$data['headers'][] = array(
					'k' => $header[ 'k' ],
					'v' => $header[ 'v' ],
					'i' => $header[ 'i' ]
				);
			}
		}

		if( $this->body ){
			$data['body'] = $this->body->serialize( $content_type );
		}

		if( $content_type = $this->getContentType() ){
			$data['content_type'] = $content_type;
		}

		return $data;
	}

	public function __toString(){
		throw new Exception( 'Not implemented' );
	}

	protected function getPath(){
		throw new Exception( 'Unsupported: HTTP_Response.getPath()' );
	}

	protected function getQueryString(){
		throw new Exception( 'Unsupported: HTTP_Response.getQueryString()' );
	}
}
