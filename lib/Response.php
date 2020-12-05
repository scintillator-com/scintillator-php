<?php
final class Response{
	public $content = null;
	public $contentType = null;
	public $formatter = null;
	public $headers = array();
	
	public final function __construct( $content=null, $contentType=null ){
		$this->content = $content;
		$this->setContentType( $contentType );
	}

	public static final function Create( $content=null, $contentType=null ){
		return new Response( $content, $contentType );
	}

	public static final function Create204(){
		header('HTTP/1.1 204 No Content', true, 204);
		//flush();
		//ignore_user_abort( true );
	}

	public static final function Create400( $contentType=null ){
		return new Response( new Exception( 'Bad Request', 400 ), $contentType );
	}

	public static final function Create404( $contentType=null ){
		return new Response( new Exception( 'Not Found', 404 ), $contentType );
	}

	public static final function Create405( $contentType=null, $method=null ){
		if( $method )
			return new Response( new Exception( "Method Not Allowed: {$method}", 405 ), $contentType );
		else
			return new Response( new Exception( "Method Not Allowed", 405 ), $contentType );
	}

	public static final function Create500( $contentType=null ){
		return new Response( new Exception( 'Internal Server Error', 500 ), $contentType );
	}

	public final function emit(){
		foreach( $this->headers as $k => $v ){
			if( is_numeric( $k ) )
				header( $v );
			else
				header( "{$k}: {$v}" );
		}

		try{
			$this->formatter->emit( $this->content );
			return $this;
		}
		catch( Exception $ex ){
			try{
				$res = Response::Create( $ex, $this->contentType );
				$res->emit();
				return $res;
			}
			catch( Exception $ex ){
				try{
					$res = Response::Create500( $this->contentType );
					$res->emit();
					return $res;
				}
				catch( Exception $ex ){
					header( 'Content-Type: text/plain', true, 500 );
					header( 'Content-Length: 26' );
					echo '500: Internal Server Error';
					exit;
				}
			}
		}
	}

	public final function setContentType( $contentType ){
		if( $contentType ){
			$this->contentType = strtolower( trim( $contentType ) );
		}
		else if( function_exists( 'getallheaders' ) ){
			foreach( getallheaders() as $k => $v ){
				if( strcasecmp( $k, 'accept' ) === 0 ){
					$this->contentType = strtolower( trim( $v ) );
					break;
				}
			}
		}
		else if( !empty( $_SERVER[ 'HTTP_ACCEPT' ] ) ){
			$this->contentType = strtolower( trim( $_SERVER[ 'HTTP_ACCEPT' ] ) );
		}

		$this->_setFormatter();
	}
	
	private final function _setFormatter(){
		$types = parse_tuple_header( $this->contentType );

		foreach( $types as $type => $attributes ){
			switch( $type ){
				case 'json':
				case 'application/json':
					$this->formatter = new Formatter_JSON();
					return;

				//case 'text/html':
				//case 'application/xhtml+xml':

				case '*/*':
				case 'text':
				case 'text/plain':
					$this->formatter = new Formatter_Text();
					return;
			}
		}
		
		throw new Exception( 'No supported formats', 400 );
	}
}
