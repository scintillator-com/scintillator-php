<?php
abstract class Route{
	protected $optional = array();
	protected $pageable = null;
	protected $request  = null;
	protected $required = array();
	protected $response  = null;

	public function __construct( Request $request ){
		$this->request  = $request;
		$this->response = new \Response();

		if( !empty( $request->headers['accept'] ) ){
			//\Log::warning( $request->headers['accept'] );
			$this->response->setContentType( $request->headers['accept'] );
		}
	}

	protected final function json(){
		if( $this->request->method !== 'GET' ){
			if( !empty( $this->request->headers['content-type'] ) ){
				if( !$this->request->isContentType( 'application/json' ) )
					throw new Exception( "Unsupported content type: {$this->request->headers['content-type']}", 422 );
			}
			else
				throw new Exception( "Unspecified content type", 422 );


			$this->request->data = $this->request->getParseJSON();
			if( empty( $this->request->data ) )
				throw new Exception( "Can't parse data", 422 );
		}

		$this->response->setContentType( 'application/json' );
		return $this;
	}

	public function OPTIONS(){
		$this->response->formatter = new Formatter_Empty();
		header( 'HTTP/1.1 204 No Content', true, 204 );
	}

	public final function process(){
		if( $this->request->isDebug() ){
			Log::$Level = IP_ERROR_ALL;
		}

		Log::debug( "REQUEST:   {$this->request}" );
		if( !method_exists( $this, $this->request->method ) ){
			$this->response = \Response::Create405( $this->request->method );
			return;
		}

		try{
			$this->{$this->request->method}();
		}
		catch( Exception $ex ){
			//\Log::error( $ex );
			//TODO: clone?  track old response?
			$this->response->emitException( $ex );
		}
	}

	protected final function pageable( $default, $max ){
		$this->pageable = true; //compact( 'default', 'max' );

		$this->optional[ 'page'     ] = array( 'format' => 'integer', 'default' => 1,        'range' => array( 1, PHP_INT_MAX ), 'scalar' );
		$this->optional[ 'pageSize' ] = array( 'format' => 'integer', 'default' => $default, 'range' => array( 1, $max ), 'scalar' );
		return $this;
	}

	/*
	* Function validate
	* Validate parameters given
	* @param (array) $requestParameters - parameters given
	*/
	protected final function validate( $requestData = null ){
		$data = isset( $requestData ) ? $requestData : $this->request->data;
		return \Validator::validate( $data, $this->required, $this->optional );
	}
}
