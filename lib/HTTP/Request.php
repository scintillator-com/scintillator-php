<?php

//TODO: capture EOL?
class HTTP_Request extends HTTP_Data{
	//shared with HTTP_Data
	protected $path   = null;
	protected $_query  = '';
	protected $scheme = null;

	private $_authUser = null;
	private $_authPass = null;
	private $http_version = null;
	private $port   = null;
	private $verb   = null;

	public function getBody(){
		return $this->_body;
	}

	public function getHost(){
		if( $header = $this->getHeader( 'HOST' ) ){
			return $header['v'];
		}
		else{
			return null;
		}
	}

	public function getVerb(){
		return $this->verb;
	}

	public function isHostSelf(){
		$host = trim( $this->getHost() );
		list( $host ) = explode( ':', $host );
		if( empty( $host ) )
			return true;

Log::warn(array( $host, $_SERVER['SERVER_ADDR'], $_SERVER['SERVER_NAME'] ));
		if( in_array( $host, array( '3.23.176.78', '172.31.41.63', $_SERVER['SERVER_ADDR'], $_SERVER['SERVER_NAME'] )))
			return true;

		return false;
	}

	public static function load(){
		$request = new HTTP_Request();
		$request->loadHeaders( getallheaders() );

		$request->http_version = $_SERVER[ 'SERVER_PROTOCOL' ];
		$request->port = (int)$_SERVER['SERVER_PORT'];
		$request->scheme = $_SERVER[ 'REQUEST_SCHEME' ];
		$request->verb = $_SERVER[ 'REQUEST_METHOD' ];

		if( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) )
			$request->_authUser = $_SERVER[ 'PHP_AUTH_USER' ];

		if( isset( $_SERVER[ 'PHP_AUTH_PW' ] ) )
			$request->_authPass = $_SERVER[ 'PHP_AUTH_PW' ];


		$path = $_SERVER['REQUEST_URI'];
		$queryAt = strpos( $path, '?' );
		if( $queryAt === false ){
			$request->path = $path;
		}
		else{
			$request->_query = substr( $path, $queryAt );
			$request->path = substr( $path, 0, $queryAt );
		}

		return $request;
	}

	public function loadBody( $reader=null ){
		$reader = fopen( 'php://input', 'r' );
		parent::loadBody( $reader );
		fclose( $reader );
	}

	public function relayCurl(){
		$relay = HTTP_Relay::create( 'CURL' );
		$relay->loadRequest( $this );
		$response = $relay->send();
		return $response;
	}

	public function relaySocket(){
		throw new Exception( 'Not implemented' );
	}

	public function serialize(){
		$content_type = $this->getContentType();

		$data = array(
			'created' => new MongoDB\BSON\UTCDateTime(),
			'http_version' => $this->http_version,
			'verb'    => $this->verb,
			'scheme'  => $this->scheme,
			'host'    => $this->getHost(),
			'port'    => $this->port,
			'path'    => $this->path,
			'queryString' => !empty( $this->_query ) ? $this->_query : null,
			'content_length' => 0,
			'content_type' => $content_type,
			'headers' => array(),
			'query'   => array(),
			'body'    => null,
		);

		if( !empty( $this->_headers ) ){
			foreach( $this->_headers as &$header ){
				$data['headers'][] = array(
					'k' => $header[ 'k' ],
					'v' => $header[ 'v' ],
					'i' => $header[ 'i' ]
				);
			}
		}

		if( !empty( $this->_query ) ){
			if( $this->_query[0] === '?' )
				$data['query'] = $this->_parseQuery( substr( $this->_query, 1 ) );
			else
				$data['query'] = $this->_parseQuery( $this->_query );
		}

		if( $this->_body ){
			$data['body'] = $this->_body->serialize( $content_type );
		}

		if( $content_type = $this->getContentType() ){
			$data['content_type'] = $content_type;
		}

		return $data;
	}

	public function setHost( $host ){
		$this->setHeader( 'Host', $host );
		return $this;
	}

	public function setPort( $port ){
		$this->port = $port;
		return $this;
	}

	public function setScheme( $scheme ){
		$this->scheme = $scheme;
		return $this;
	}

	public function __toString(){
		$query = $this->_query; //str_replace( '&', '&amp; ', $this->_query );

		ob_start();
		echo "{$this->verb} {$this->path}{$query} {$this->http_version}". PHP_EOL;
		if( !$this->getHeader( 'AUTHORIZATION' ) ){
			if( isset( $this->_authUser ) ){
				$host = $this->getHeader( 'HOST' );
				if( !empty( $this->_authPass ) ){
					echo "Host: {$this->_authUser}:{$this->_authPass}@{$host['v']}". PHP_EOL; 
				}
				else{
					echo "Host: {$this->_authUser}@{$host['v']}". PHP_EOL; 
				}
			}
			else if( isset( $this->_authPass ) ){
				echo "Host: :{$this->_authPass}@{$host['v']}". PHP_EOL; 
			}
		}

		//HeaderCollection?
		foreach( $this->_headers as &$h ){
			echo "{$h['k']}: {$h['v']}". PHP_EOL;
		}

		if( $this->_body )
			echo PHP_EOL . "{$this->_body}";
		else
			echo PHP_EOL . PHP_EOL;

		return ob_get_clean();
	}

	private function _parseQuery( $query ){
		$data = array();
		$pairs = explode('&', $query);
		foreach( $pairs as &$pair ){
			list( $key, $value ) = explode( '=', $pair, 2 );
			$data[] = array(
				'k' => $key,
				'v' => $value,
				'i' => count( $data )
			);
		}

		return $data;
	}
}

