<?php
class HTTP_Relay_Curl extends HTTP_Relay{
	private $_curl = null;
	private $_options = array();
	private $_responseFD = null;
	private $_responsePath = null;

	private function applyHeaders( HTTP_Request $request ){
		$headers = array();
		foreach( $request->getHeaders() as &$header ){
			if( strcasecmp( 'USER-AGENT', $header['key'] ) === 0 )
				$this->_options[ CURLOPT_USERAGENT ] = $header['value'];
			else
				$headers[] = "{$header['key']}: {$header['value']}";
		}

		if( $headers ){
			$this->_options[ CURLOPT_HTTPHEADER ] = $headers;
		}
	}

	public function loadRequest( HTTP_Request $request ){
		//TODO: cookies
		$forceHttps = false;
		$upgrade = $request->getHeader( 'UPGRADE-INSECURE-REQUESTS' );
		if( $upgrade && (int)$upgrade['value'] )
			$forceHttps = true;

		//reset + url
		$this->_options = array(
			CURLOPT_HTTPGET => false,
			CURLOPT_POST    => false,
			CURLOPT_PUT     => false,
			CURLOPT_URL     => $request->getUrl( $forceHttps )
		);

		if( true ){
			//save headers + body to file
			$this->_responsePath = tempnam( sys_get_temp_dir(), 'curl_' );
			$this->_responseFD = fopen( $this->_responsePath, 'w+' );
			$this->_options[ CURLOPT_FILE        ] = $this->_responseFD;
			$this->_options[ CURLOPT_WRITEHEADER ] = $this->_responseFD;
		}
		else{
			//return headers + body from curl_exec
			$this->_options[ CURLOPT_HEADER         ] = true;
			$this->_options[ CURLOPT_RETURNTRANSFER ] = true;
		}

		$this->applyHeaders( $request );


		$verb = $request->getVerb();
		switch( $verb ){
			case 'GET':
				$this->_options[ CURLOPT_HTTPGET ] = true;
				break;

			case 'HEAD':
				$this->_options[ CURLOPT_NOBODY ] = true;
				break;

			case 'POST':
				$this->_options[ CURLOPT_POST ] = true;
				$this->loadRequestBody( $request );
				break;

			case 'PUT':
				$this->_options[ CURLOPT_PUT ] = true;
				$this->loadRequestBody( $request );
				break;

			case 'DELETE':
			case 'OPTIONS':
			case 'PATCH':
				$this->_options[ CURLOPT_CUSTOMREQUEST ] = $verb;
				$this->loadRequestBody( $request );
				break;

			default:
				throw new Exception( "Not implemened: {$verb}" );
		}

		return $this;
	}

	public function send(){
		$response = null;

		try{
			$this->_curl = curl_init();
			if( !curl_setopt_array( $this->_curl, $this->_options ) )
				throw new CurlException( curl_error( $this->_curl ) );


			Log::warning( "Writing headers and response to {$this->_responsePath}" );
			if( curl_exec( $this->_curl ) )
				fflush( $this->_responseFD );
			else
				throw new CurlException( curl_error( $this->_curl ) );


			$response = HTTP_Response::loadStream( $this->_responseFD );
		}
		catch( Exception $ex ){
			throw $ex;
		}
		finally{
			fclose( $this->_responseFD );
			unlink( $this->_responsePath );	
		}
		
		//TODO: get timing
		//$info = curl_getinfo( $this->_curl );
		return $response;
	}

	private function loadRequestBody( HTTP_Request &$request ){
		if( $request->hasBody() ){
			//ref: https://www.php.net/manual/en/function.curl-setopt  CURLOPT_POSTFIELDS
			//As with curl_setopt(), passing an array to CURLOPT_POST will encode the data as multipart/form-data,
			//while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
			if( $request->getContentType() === HTTP_Data::MULTIPART_FORMDATA ){
				$this->_options[ CURLOPT_POSTFIELDS ] = $request->getBodyArray();
			}
			else{
				$this->_options[ CURLOPT_POSTFIELDS ] = $request->getBodyString();
			}
		}
	}
}
