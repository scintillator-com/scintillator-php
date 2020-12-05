<?php
abstract class Route{
	protected $optional = array();
	protected $pageable = null;
	protected $request  = null;
	protected $required = array();

	public final function __construct( Request $request ){
		$this->request = $request;
		$this->response = Response::Create( null, $request->accept );
	}

	public final function emit(){
		$this->response->emit();
	}

	public final function process(){
		if( $this->request->isDebug() ){
			Log::$Level = IP_ERROR_ALL;
		}

		Log::debug( "REQUEST:   {$this->request}" );
		if( !method_exists( $this, $this->request->method ) ){
			$this->response = Reasponse::Create405( $this->request->method );
			return;
		}

		try{
			$this->response->content = $this->{$this->request->method}();
		}
		catch( Exception $ex ){
Log::error( $ex->getCode() .': '. $ex->getMessage() );
			$this->response->content = $ex;
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
		return RequestValidator::validate( $data, $this->required, $this->optional );
	}
}
