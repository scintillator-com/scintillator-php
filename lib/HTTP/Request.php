<?php

//TODO: capture EOL?
class HTTP_Request extends HTTP_Data{
	//inherited:
	//protected $_body = '';
	//protected $_bodyBoundary = null;
	//protected $_bodyPos = -1;
	//protected $content_length = -1;
	//protected $headers = array();
	//protected $_headerIndex = array();


	private $_authUser = null;
	private $_authPass = null;
	private $http_version = null;
	private $method   = null;
	private $path   = null;
	private $port   = null;
	private $query_data  = array();
	private $query_string = '';
	private $scheme = null;

	public function getBody(){
		return $this->body;
	}

	public function getHost(){
		if( $header = $this->getHeader( 'HOST' ) ){
			return $header['v'];
		}
		else{
			return null;
		}
	}

	public function getUrl( $forceHttps = false ){
		$scheme = $forceHttps ? 'https' : $this->scheme;

		$userPass = '';
		$up = $this->getUserPass();
		$auth = $this->getHeader( 'AUTHORIZATION' );
		if( !$auth && $up )
			$userPass = "{$up}@";

		$host = $this->getHeader( 'HOST' );

		$qs = '';
		if( $this->query_string )
			$qs = "?{$this->query_string}";

		$url = "{$scheme}://{$userPass}{$host['v']}{$this->path}{$qs}";
		return $url;
	}

	public function getPath(){
		return $this->path;
	}

	public function getQueryString(){
		return $this->query_string;
	}

	public function getVerb(){
		return $this->method;
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
		$request->method = $_SERVER[ 'REQUEST_METHOD' ];

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
			$request->query_string = substr( $path, $queryAt + 1 );
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
		$data = array(
			'created' => new MongoDB\BSON\UTCDateTime(),
			'http_version' => $this->http_version,
			'method'  => $this->method,
			'scheme'  => $this->scheme,
			'host'    => $this->getHost(),
			'port'    => $this->port,
			'path'    => $this->path,
			'content_length' => 0,
			'content_type' => $this->getContentType(),
			'headers'      => array(),
			'query_data'   => $this->query_data,
			'query_string' => $this->query_string,
			'body' => null,
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

		if( !empty( $this->query_string ) ){
			if( $this->query_string[0] === '?' )
				throw new Exception( "Why is there a ? here?" );

			$data['query_data'] = $this->_parseQuery( $this->query_string );
		}

		if( $this->body ){
			$data['body'] = $this->body->serialize( $content_type );
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
		echo "{$this->method} {$this->path}{$query} {$this->http_version}". PHP_EOL;
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
		foreach( $this->headers as &$h ){
			echo "{$h['k']}: {$h['v']}". PHP_EOL;
		}

		if( $this->body )
			echo PHP_EOL . "{$this->body}";
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

