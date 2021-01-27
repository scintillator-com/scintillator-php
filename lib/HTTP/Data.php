<?php

abstract class HTTP_Data{
	const APPLICATION_JSON   = 'application/json';
	const FORM_URL_ENCODED   = 'application/x-www-form-urlencoded';
	const MULTIPART_FORMDATA = 'multipart/form-data';
	const TEXT_PLAIN = 'text/plain';

	protected $body = '';
	protected $_bodyBoundary = null;
	protected $_bodyPos = -1;

	protected $headers = array();
	protected $_headerIndex = array();

	protected $content_length = 0;
	private $content_type = null;


	public abstract function __toString();


	public function getBody(){
		return $this->body;
	}

	public function getContentType(){
		return $this->content_type;
	}

	public function getHeader( $key ){
		$key = strtoupper( $key );
		if( array_key_exists( $key, $this->_headerIndex ) ){
			$idx = $this->_headerIndex[ $key ];
			return $this->headers[ $idx ];
		}
		else{
			return null;
		}
	}

	public function getHeaders(){
		$clone = array();
		foreach( $this->headers as $key => &$item ){
			$clone[] = array(
				'k' => $item['k'],
				'v' => $item['v'],
				'i' => $item['i']
			);
		}
		return $clone;
	}

	public function hasBody(){
		return $this->content_length > 0;
	}

	//TODO: we should line this up with the python addon
	public function measureBody(){
		$reader = fopen( 'php://input', 'r' );
		while( !feof( $reader ) ){
			fgets( $reader );
		}
		$body_length = ftell( $reader );
		fclose( $reader );

		if( $body_length != $this->content_length ){
			\Log::warning( "Body length {$body_length} doesn't match content length {$this->content_length}" );
			$this->content_length = $body_length;
		}

		return $body_length;
	}

	//CEE: necessarily, this happens after loadHeaders, so for now we measure that
	//TODO: we should line this up with the python addon
	public function measureHeaders(){
		$length = 0;
		foreach( $this->headers as &$h ){
			$length += strlen( $h['k'] ) + 1 + strlen( $h['v'] );
		}
		return $length;
	}

	public static function readHeaderStream( &$stream ){
		$headers = array();
		while( !feof( $stream ) ){
			if( $line = trim( fgets( $stream ) ) ){
				if( $foundAt = strpos( $line, ':' ) ){
					$key   = trim( substr( $line, 0, $foundAt  ) );
					$value = trim( substr( $line, $foundAt + 1 ) );
					if( array_key_exists( $key, $headers ) )
						Log::warning( "Overwriting header: '{$key}'". PHP_EOL ."\t". $line );

					$headers[ $key ] = $value;
				}
				else{
					Log::warning( "Line doesn't appear to be a header:". PHP_EOL ."\t". $line );
				}
			}
			else{
				break;
			}
		}

		return $headers;
	}

	protected function getUserPass(){
		if( isset( $this->_authUser ) ){
			if( !empty( $this->_authPass ) )
				return "{$this->_authUser}:{$this->_authPass}";
			else
				return "{$this->_authUser}"; 
		}
		else if( isset( $this->_authPass ) ){
			return "{$this->_authPass}"; 
		}
		else{
			return '';
		}
	}

	protected function loadBody( $reader ){
		$boundary = null;
		$contentType = $this->getContentType();
		if( $contentType === self::MULTIPART_FORMDATA ){
			$header = $this->getHeader( 'CONTENT-TYPE' );
			if( $foundAt = stripos( $header['v'], 'boundary=' ) ){
				$boundary = substr( $header['v'], $foundAt + 9 );
			}
		}

		$initPos = ftell( $reader );
		$boundaryCount = 0;
		while( !feof( $reader ) ){
			$line = fgets( $reader );
			if( $boundary ){
				if( strpos( $line, $boundary ) !== false )
					++$boundaryCount;
			}
			//else{
			//	$boundary = trim( $line );
			//}
		}


		$stringBody = '';
		$attachments = array();
		$this->content_length = ftell( $reader );
		if( $this->content_length ){
			$this->_bodyPos = 0;
			fseek( $reader, $initPos );
			if( $boundary && $boundaryCount ){
				$this->_bodyBoundary = $boundary;

				while( !feof( $reader ) ){
					$line = fgets( $reader );
					//Log::info( $line );
					$pos = strpos( $line, $boundary );
					if( strpos( $line, $boundary ) !== false ){
						if( $att = HTTP_Attachment::loadStream( $reader, $boundary ) ){
							$att->index = count( $attachments );
							$attachments[] = $att;
						}
					}
				}
			}
			else{
				$stringBody = fread( $reader, $this->content_length );
			}
		}


		if( $attachments ){
			$this->body = new HTTP_MultipartBody( $attachments, $boundary );
		}
		else if( $stringBody ){
			$this->body = new HTTP_StringBody( $stringBody );
		}
		else if( $_POST || $_FILES ){
			$this->content_type = self::MULTIPART_FORMDATA;
			$this->body = HTTP_MultipartBody::loadPhp( $boundary );
		}

		if( empty( $this->content_type ) && $stringBody ){
			$tmp = json_decode( $stringBody, true );
			if( isset( $tmp ) ){
				$this->content_type = self::APPLICATION_JSON;
			}
			else if( is_null( $tmp ) && !json_last_error() ){
				$this->content_type = self::APPLICATION_JSON;
			}
			else if( strpos( $stringBody, '=' ) !== false ){
				parse_str( $stringBody, $tmp );
				if( !empty( $tmp ) ){
					$this->content_type = self::FORM_URL_ENCODED;
				}
			}
		}
	}

	protected function loadHeaders( $headers ){
		foreach( $headers as $key => &$value ){
			if( !$value )
				continue;


			$KEY = strtoupper( $key );
			if( array_key_exists( $KEY, $this->_headerIndex ) )
				Log::warning( "Overwriting header: '{$key}'". PHP_EOL );


			$this->_headerIndex[ $KEY ] = count( $this->headers );
			$header = array(
				'k'  => $key,
				'v'  => $value,
				'i'  => count( $this->headers ),
				//'parsed' => array()
			);

			if( strpos( $value, ';' ) !== false ){
				$attributes = preg_split( '/;\s*/', $value );
				$header['parsed'][0] = array_shift( $attributes );
				foreach( $attributes as &$att ){
					if( strpos( $att, '=' ) !== false ){
						$data = parse_ini_string( $att );
						if( $data ){
							$header['parsed'][ key( $data ) ] = current( $data );
						}
					}
				}
			}

			$this->headers[] = $header;

			if( $KEY === 'CONTENT-LENGTH' ){
				$this->content_length = (int)$value;
			}
			else if( $KEY === 'CONTENT-TYPE' ){
				if( strcasecmp( self::APPLICATION_JSON, $value ) === 0 ){
					$this->content_type = self::APPLICATION_JSON;
				}
				else if( strcasecmp( self::FORM_URL_ENCODED, $value ) === 0 ){
					$this->content_type = self::FORM_URL_ENCODED;
				}
				else if( strncasecmp( self::MULTIPART_FORMDATA, $value, 19 ) === 0 ){ //|| stripos( $header['v'], 'boundary' ) ){
					$this->content_type = self::MULTIPART_FORMDATA;
				}
				else if( strcasecmp( self::TEXT_PLAIN, $value ) === 0 ){
					$this->content_type = self::TEXT_PLAIN;
				}
				else{
					//check for ';'
					Log::warning( "Content-Type: {$header['v']}" );
					$this->content_type = $header['v'];
				}
			}
		}
	}

	protected function setHeader( $key, $value ){
		$k = strtoupper( $key );
		if( array_key_exists( $k, $this->_headerIndex ) ){
			$idx = $this->_headerIndex[ $k ];
			$this->headers[ $idx ][ 'v' ] = $value;
		}
		else{
			$idx = count( $this->headers );
			$this->_headerIndex[ $k ] = $idx;
			$this->headers[ $idx ] = array(
				'k' => $key,
				'v' => $value,
				'i' => $idx
			);
		}
	}
}
