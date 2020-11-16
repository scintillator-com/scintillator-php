<?php
final class Request{
	public $headers = array();
	public $method = 'UNKNOWN';

	public $fullPath = null;
	public $path = null;

	public $urlArgs = array();

	public $data = array();
	public $rawData = null;

	public $accept = null;
	public $contentType = null;

	public function __construct(){
	}

	public function getParseJSON(){
		$this->rawData = file_get_contents( 'php://input' );

		$data = json_decode( $this->rawData, true);
		if( empty( $data ) ){
			switch( json_last_error() ){
				case JSON_ERROR_NONE:
					break;

				case JSON_ERROR_DEPTH:
					throw new IntelePeer_Exception( "Server received complex JSON from client, reduce nesting.", 400 );

				case JSON_ERROR_STATE_MISMATCH:
				case JSON_ERROR_SYNTAX:
					throw new IntelePeer_Exception( "Server received malformed JSON from client.", 400 );

				case JSON_ERROR_CTRL_CHAR:
					throw new IntelePeer_Exception( "Server received unexpected control character from client.", 400 );

				case JSON_ERROR_UTF8:
					throw new IntelePeer_Exception( "Server received malformed UTF-8 encodings from client.", 400 );

				default:
					$this->throwBadRequest();
			}
		}

		return $data;
	}

	/*
	 * Function isContentType
	 * Check header 'ContentType' to be the $type required
	 * @param (string) $type - ContentType header content required
	 * @return (boolean)
	*/
	public function isContentType( $type ){
		if( empty( $this->headers[ 'Content-Type' ] ) )
			return false;

		if( strncmp( $this->headers[ 'Content-Type' ], $type, strlen( $type ) ) != 0 )
			return false;

		return true;
	}

	public function isDebug(){
		if( array_key_exists( 'DEBUG', $_GET ) )
			return true;

		if( is_array( $this->data ) && array_key_exists( 'DEBUG', $this->data ) )
			return true;

		return false;
	}

	public static function Load(){
		$request = new Request();

		$basePath = rtrim( $_SERVER[ 'DOCUMENT_ROOT' ], '/' );
		$rebaseAt = strpos( $_SERVER[ 'SCRIPT_FILENAME' ], $basePath );
		if( $rebaseAt != 0 )
			$request->throwBadRequest();


		$baseURL = dirname( substr( $_SERVER[ 'SCRIPT_FILENAME' ], strlen( $basePath ) ) );
		$rebaseAt = strpos( $_SERVER[ 'REQUEST_URI' ], $baseURL );
		if( $rebaseAt != 0 )
			$request->throwBadRequest();


		$urlData = parse_url( $_SERVER[ 'REQUEST_URI' ] );
		$request->fullPath = $urlData[ 'path' ];
		$request->path = substr( $urlData[ 'path' ], strlen( $baseURL ) );


		$headers = array();
		if( function_exists( 'getallheaders' ) ){
			$headers = getallheaders();
		}else{
			$headers[ 'Accept' ] = $_SERVER[ 'HTTP_ACCEPT' ];
			$headers[ 'Content-Type' ] = $_SERVER[ 'CONTENT_TYPE' ];
			if( !empty( $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ] ) ){
				$headers[ 'Authorization' ] = $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ];
			}
		}

		$request->setHeaders( $headers );
		$request->setMethod( $_SERVER[ 'REQUEST_METHOD' ] );

		/*
		if( $request->isContentType( 'application/json' ) ){
			$request->data = $request->getParseJSON();
		}
		*/

		return $request;
	}

	private function setHeaders( $headers ){
		$newHeaders = array();
		foreach( $headers as $key => &$value ){
			$newHeaders[ strtolower( $key ) ] = $value;
		}

		if( !empty( $newHeaders['content-type'] ) ){
			$this->_contentType = strtolower( trim( $newHeaders['content-type'] ) );
		}

		if( !empty( $newHeaders['accept'] ) ){
			$this->_accept = strtolower( trim( $newHeaders['accept'] ) );
		}

		$this->headers = $newHeaders;
		ksort( $this->headers );
	}

	private function setMethod( $method ){
		$this->method = $method;
	}

	public function throwBadRequest(){
		throw new IntelePeer_Exception( "Bad Request", 400 );
	}

	public function __toString(){
		ob_start();
		echo "{$this->method} {$this->fullPath}". ( !empty( $_SERVER[ 'QUERY_STRING' ] ) ? "?{$_SERVER[ 'QUERY_STRING' ]}" : '' ) . PHP_EOL;
		foreach( $this->headers as $k => &$v ){
			if( strncasecmp( $k, 'Cookie', 6 ) != 0 ){
				echo "{$k}: {$v}". PHP_EOL;
			}
		}

		echo PHP_EOL;
		echo json_encode( $this->data );
		return ob_get_clean();
	}
}

