<?php
final class Response{
	public $formatter;
	public $cors = array();
	public $headers = array();

	private $_contentType;
	
	public final function __construct( $contentType='*/*' ){
		$this->setContentType( $contentType );
	}

	public final function clearHeaders(){
		$this->headers = array();
		return $this;
	}

	public final function cors( $methods, $origin='*' ){
		$this->cors = array(
			'headers' => array(),
			'methods' => (array)$methods,
			'origin'  => $origin
		);
		return $this;
	}

	public final function emit( $content, $code=200 ){
		//emit regular content
		try{
			//might performs rendering to get Content-Length
			$contentHeaders = $this->formatter->getHeaders( $content );
			$this->_emitHeaders( $contentHeaders, $code );
			$this->_emitContent( $content );
		}
		catch( Exception $ex ){
			//TODO: clone?  track old response?
			\Log::error( "Exception during emit( content ): {$ex}" );
			$this->formatter->clearCache();
			$this->emitException( $ex );
		}
	}

	public final function emitException( Exception &$ex ){
		$code = $ex->getCode();
		if( !( 400 <= $code && $code < 600 ) ){
			\Log::error( "Non-HTTP exception: {$ex}" );

			$code = 500;
			$ex = new Exception( 'Internal Server Error', 500, $ex );
		}

		//emit error from formatting
		try{
			$exHeaders = $this->formatter->getHeaders( $ex );
			$this->_emitHeaders( $exHeaders, $code );
			$this->_emitContent( $ex );
			return;
		}
		catch( Exception $ex ){
			\Log::error( "Exception during emit( exception ): {$ex}" );
			$this->formatter->clearCache();
		}

		//bail
		header( 'Content-Type: text/plain', true, 500 );
		header( 'Content-Length: 26' );
		echo '500: Internal Server Error';
	}

	public final function loadContentType(){
		if( function_exists( 'getallheaders' ) ){
			foreach( getallheaders() as $k => &$v ){
				if( strcasecmp( $k, 'accept' ) === 0 ){
					$this->setContentType( $v );
					break;
				}
			}
		}
		else if( !empty( $_SERVER[ 'HTTP_ACCEPT' ] ) ){
			$this->setContentType( $_SERVER[ 'HTTP_ACCEPT' ] );
		}
		else{
			throw new Exception( 'Accept header not found' );
		}

		return $this;
	}

	public final function setContentType( $contentType ){
		$this->_setFormatter( strtolower( trim( $contentType ) ) );
		return $this;
	}

	private final function _emitContent(){
		echo $this->formatter->format( $this->content );
	}

	private final function _emitHeaders( $contentHeaders, $code ){
		http_response_code( $code );

		if( !empty( $this->cors ) ){
			if( $_SERVER[ 'REQUEST_METHOD' ] === 'OPTIONS' ){
				$methods = implode( ',', $this->cors['methods'] );
				header( "Access-Control-Allow-Methods: {$methods}" );
			}

			//if( !empty( $this->cors['headers'] ) ){
			//	$headers = implode( ',', $this->cors['headers'] );
			//	header( "Access-Control-Allow-Headers: {$headers}" );
			//}

			if( !empty( $this->cors['origin'] ) ){
				header( "Access-Control-Allow-Origin: {$this->cors['origin']}" );
			}
		}

		foreach( $this->headers as $k => $v ){
			header( is_numeric( $k ) ? $v : "{$k}: {$v}" );
		}

		foreach( $contentHeaders as $k => $v ){
			header( is_numeric( $k ) ? $v : "{$k}: {$v}" );
		}
	}

	private final function _setFormatter( $contentType ){
		$types = parse_tuple_header( $contentType );
		foreach( $types as $type => $attributes ){
			switch( $type ){
				case 'json':
				case 'application/json':
					$this->_contentType = 'application/json';
					$this->formatter = new Formatter_JSON();
					return;

				//case 'text/html':
				//case 'application/xhtml+xml':

				case '*/*':
				case 'text':
				case 'text/plain':
					$this->_contentType = 'text/plain';
					$this->formatter = new Formatter_Text();
					return;
			}
		}

		throw new Exception( 'No supported formats', 400 );
	}
}
