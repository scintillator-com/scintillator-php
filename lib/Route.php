<?php
abstract class Route{
	protected $request = null;
	protected $required = array();
	protected $optional = array();

	public function __construct( Request $request ){
		$this->request = $request;
		$this->response = Response::Create( null, $request->accept );
	}

	public function emit(){
		$this->response->emit();
	}

	public function process(){
		//if( $this->request->IsDebug() ){
		//	Log::$Level = IP_ERROR_ALL;
		//}

		Log::debug( "REQUEST:   {$this->request}" );
		if( !method_exists( $this, $this->request->method ) ){
			$this->response = Reasponse::Create405( $this->request->method );
			return;
		}

		try{
			$this->response->content = $this->{$this->request->method}();
		}
		catch( Exception $ex ){
			/*
			$code = $ex->getCode();
			$message = $ex->getMessage();
			$this->sendError( $message, $code );
			*/
		
			$http_code = 500;
			$code = $ex->getCode();
			if( empty( $code ) ){
				$code = 500;
			}
			else if( 400 <= $code && $code < 600 ){
				$http_code = $code;
			}

			$message = $ex->getMessage();
			if( empty( $message ) ){
				$message = 'Internal Server Error';
			}

			$response = compact( 'code', 'message' );
			$this->sendResponse( $response, $http_code );
		}
	}

	protected function processRequest(){
		$data = array();
		$callback = null;
		switch( $this->request->verb ){
			case 'DELETE':
				$callback = array( $this, 'DELETE' );
				break;

			case 'GET':
				$callback = array( $this, 'GET' );
				$data = $_GET;
				break;

			case 'PATCH':
				$callback = array( $this, 'PATCH');
				break;

			case 'POST':
				$callback = array( $this, 'POST' );
				$data = $_POST;
				break;

			case 'PUT':
				$callback = array( $this, 'PUT' );
				break;

			default:
				header( 'Accept: DELETE, GET, PATCH, POST, PUT', true, 405 );
				throw new Exception( 'Method Not Allowed', 405 );
		}

		if( !is_callable( $callback ) )
			throw new Exception( "Not Found", 404 );

		try{
			$response = call_user_func( $callback, $data );
			if( !is_null( $response ) ){
				if( $this->_isCacheable && !$this->_fromCache ){
					$this->setCache( $response );
				}

				$this->sendResponse( $response );
			}
		}
		catch( NotImplementedException $nex ){
			$this->throw501( $nex );
		}
		catch( Exception $ex ){
			$code      = $ex->getCode();
			$message   = $ex->getMessage();
			$exception = isset( $ex->exception ) ? $ex->exception : '';
			$details   = isset( $ex->details   ) ? PHP_EOL ."\t". $ex->details : '';
			$logStr    = "{$exception}{$code}: {$message}{$details}";

			$tmp = $ex;
			while( $tmp ){
				$file = $tmp->getFile();
				$file = !empty( $file ) ? basename( $file ) : '(unknown)';
				$line = $tmp->getLine();
				$logStr .= PHP_EOL ."\tthrown from {$file}({$line})";
				$tmp = $tmp->getPrevious();
			}


			$prefix = PHP_EOL . PHP_EOL;
			if( !empty( $this->request->data[ "runAs" ] ) ){
				$logStr .= "{$prefix}runAs: {$this->request->data[ "runAs" ]}";
				$prefix = PHP_EOL;
			}

			if( !empty( $this->request->headers[ "Trackingid" ] ) ){
				$logStr .= "{$prefix}Trackingid: {$this->request->headers[ "Trackingid" ]}";
				$prefix = PHP_EOL;
			}

			if( !empty( $_SERVER[ 'REQUEST_URI' ] ) ){
				$logStr .= "{$prefix}REQUEST_URI: {$_SERVER[ 'REQUEST_URI' ]}";
				$prefix = PHP_EOL;
			}


			//4xx errors are expected
			$isClientEx = 400 <= $code && $code < 500;
			$isServerEx = 500 <= $code && $code < 600;
			$isIpEx = $ex instanceof Exception;
			if( $isClientEx ){
				Log::info( $logStr );
				throw $ex;
			}
			else if( $isServerEx ){
				if( $isIpEx ){
					Log::warn( $logStr );
					throw $ex;
				}
				else{
					Log::error( $logStr );

					switch( $code ){
						case 501: $this->throw501( $ex );
						case 503: $this->throw503( $ex );
						default:  $this->throw500( $ex );
					}
				}
			}
			else{
				Log::error( $logStr );
				$this->throw500();
			}
		}
	}

	/*
	* Function validate
	* Validate parameters given
	* @param (array) $requestParameters - parameters given
	*/
	protected function validate( $requestData = null ){
		$data = isset( $requestData ) ? $requestData : $this->request->data;
		return RequestValidator::validate( $data, $this->required, $this->optional );
	}
}
