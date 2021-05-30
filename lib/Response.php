<?php
final class Response{
	public $formatter;
	public $cors = array(
		'headers' => array()
	);
	public $headers = array();

	private $_chunks = 0;
	private $_contents = array();
	private $_contentType = null;
	private $_isChunked = null;

	private $eol = "\r\n";

	public final function __construct( $contentType='*/*' ){
		$this->setContentType( $contentType );
	}

	public final function addCors( $method, $headers=null, $origin=null ){
		$this->cors['methods'][] = $method;

		if( $headers )
			array_splice( $this->cors['headers'], count( $this->cors['headers'] ), 0, (array)$headers );

		if( $origin ){
			if( empty( $this->cors['origin'] ) ){
				$this->cors['origin'] = (string)$origin;
			}
			else if( $this->cors['origin'] === $origin ){
				//no-op
			}
			else{
				\Log::error( "Existing origin: {$this->cors['origin']};  new origin: {$origin}" );
				throw new \Exception( 'Conflicting origin' );
			}
		}
	}

	public final function clearHeaders(){
		$this->headers = array();
		return $this;
	}

	public final function cors( $methods, $headers=array(), $origin='*' ){
		$this->cors['methods'] = (array)$methods;
		$this->cors['headers'] = (array)$headers;
		$this->cors['origin']  = $origin;
		return $this;
	}

	//TODO: chunked...
	public final function dump( $content ){
		//emit regular content
		try{
			ob_start();
			dump( $content );
			$buffer = ob_get_clean();

			$this->setContentType( 'text' );
			$contentHeaders = $this->formatter->getHeaders( $buffer );
			$this->_emitHeaders( $contentHeaders, 200 );
			$this->_emitContent( $buffer );
		}
		catch( Exception $ex ){
			//TODO: clone?  track old response?
			\Log::error( "Exception during dump( content ): {$ex}" );
			$this->formatter->clearCache();
			$this->emitException( $ex );
		}

		exit;
	}

	public final function end(){
		if( $this->_isChunked ){
			$this->_emitChunk( $this->formatter->getChunksFooter() );
			$this->_emitChunk( '' );
		}

		$this->flush();
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
			$ex = new \Exception( 'Internal Server Error', 500, $ex );
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

	public final function flush(){
		ob_end_flush();
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
			throw new \Exception( 'Accept header not found' );
		}

		return $this;
	}

	public final function print( $content, $code=null, $replace=true ){
		if( headers_sent() ){
			if( $this->_isChunked ){
				//ok
			}
			else{
				\Log::error( "Output already started" );
			}
		}

		if( !$code ){
			$code = http_response_code();
		}

		if( $replace || $this->_chunks === 0 ){
			if( $this->_isChunked )
				header_remove( 'Transfer-Encoding' );
			
			$this->_chunks = 1;
			$this->_contents = array( $content );
			$this->_isChunked = false;
			$contentHeaders = $this->formatter->clearCache()->getHeaders( $content );

			ob_end_clean();
			ob_start();

			$this->_emitHeaders( $contentHeaders, $code );
			$this->_emitContent( $content );
		}
		else if( $this->_chunks === 1 ){
			$this->_chunks++;
			$this->_contents[] = $content;
			$this->_isChunked = true;
			$contentHeaders = $this->formatter->getHeaders( $content, false );

			//remove header after queue
			$this->_emitHeaders( $contentHeaders, $code );
			header_remove( 'Content-Length' );
			header( 'Transfer-Encoding: chunked' );

			$prevChunk = ob_get_clean();
			ob_start();

			$this->_emitChunk(
				$this->formatter->getChunksHeader()
				.$prevChunk
			);

			$this->_emitChunk(
				$this->formatter->getChunksSeparator()
				. $this->formatter->format( $content, false )
			);
		}
		else{
			$this->_emitChunk( 
				$this->formatter->getChunksSeparator()
				. $this->formatter->format( $content, false )
			);
		}
	}

	public final function setContentType( $contentType ){
		$this->_setFormatter( $contentType );
		return $this;
	}

	private final function _emitChunk( $chunk ){
		$hexLength = dechex( strlen( $chunk ) );
		print( "{$hexLength}{$this->eol}{$chunk}{$this->eol}" );
	}

	private final function _emitContent( $content ){
		print( $this->formatter->format( $content ) );
	}

	private final function _emitHeaders( $contentHeaders, $code ){
		http_response_code( $code );

		if( !empty( $this->cors ) ){
			if( $_SERVER[ 'REQUEST_METHOD' ] === 'OPTIONS' ){
				$methods = implode( ',', unique( $this->cors['methods'] ));
				header( "Access-Control-Allow-Methods: {$methods}" );
			}

			if( !empty( $this->cors['headers'] ) ){
				$headers = implode( ',', unique( $this->cors['headers'] ));
				header( "Access-Control-Allow-Headers: {$headers}" );
			}

			if( !empty( $this->cors['origin'] ) ){
				header( "Access-Control-Allow-Origin: {$this->cors['origin']}" );
			}
			else{
				header( "Access-Control-Allow-Origin: *" );
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
		$contentType = strtolower( trim( $contentType ) );
		$types = parse_tuple_header( $contentType );
		foreach( $types as $type => $attributes ){
			switch( $type ){
				case 'html':
				case 'text/html':
				case 'application/xhtml+xml':
					$this->_contentType = 'text/html';
					$this->formatter = new \Formatter_Text();
					return;

				case 'json':
				case 'application/json':
					$this->_contentType = 'application/json';
					$this->formatter = new \Formatter_JSON();
					return;

				case 'text':
				case 'text/plain':
					$this->_contentType = 'text/plain';
					$this->formatter = new \Formatter_Text();
					return;

				case '*/*':
				default:
					$this->_contentType = 'text/plain';
					$this->formatter = new \Formatter_Text();
					return;
			}
		}

		throw new \Exception( 'No supported formats', 400 );
	}
}
