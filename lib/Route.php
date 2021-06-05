<?php
class Route{
	use \Traits\Validated;

	protected $request  = null;
	protected $response  = null;

	private $handlers = array();

	public function __construct( \Request $request ){
		$this->request  = $request;
		$this->response = new \Response();

		if( $this->request->isDebug() )
			\Log::$Level = IP_ERROR_ALL;

		\Log::debug( "REQUEST:   {$this->request}" );
		if( !empty( $request->headers['accept'] ) ){
			//\Log::warning( $request->headers['accept'] );
			$this->response->setContentType( $request->headers['accept'] );
		}
	}

	//allow override
	public function OPTIONS(){
		//access-control-request-headers: content-type
		//access-control-request-method: POST

		//which method is it asking for?
		//Access-Control-Request-Method: POST
		
		$this->response->formatter = new \Formatters\_Empty();
		$this->response->emit( null, 204 );
	}

	public final function process(){
		if( !empty( $this->handlers[ $this->request->method ] ) ){
			\Log::debug( "'{$this->request->method}' is using handler" );

			try{
				$this->handlers[ $this->request->method ]();
			}
			catch( \Exception $ex ){
				//\Log::error( $ex );
				//TODO: clone?  track old response?
				$this->response->emitException( $ex );
			}
			return;
		}
		//legacy
		else if( method_exists( $this, $this->request->method ) ){
			if( $this->request->method === 'OPTIONS' )
				\Log::debug( "'OPTIONS' is using controller method" );
			else
				\Log::warn( "'{$this->request->method}' is controller method" );

			try{
				$this->{$this->request->method}();
			}
			catch( \Exception $ex ){
				//\Log::error( $ex );
				//TODO: clone?  track old response?
				$this->response->emitException( $ex );
			}
			return;
		}
		else{
			$this->response = \Response::Create405( $this->request->method );
		}
	}

	public final function setHandler( $method, $callback, $corsHeaders=null, $corsOrigin=null ){
		//$loadMethod = $this->request->method;
		//if( $this->request->method === 'OPTIONS' ){
		//	$tmp = $this->request->getHeader( 'access-control-Request-Method' );
		//	$loadMethod = $tmp ? strtoupper( $tmp ) : null;
		//}

		//performance: ignore the other handlers
		$method = strtoupper( trim( $method ) );
		//if( $loadMethod && $loadMethod !== $method )
		//	return;


		if( empty( $this->handlers[ $method ] ) ){
			$this->handlers[ $method ] = $callback;
			$this->response->addCors( $method, $corsHeaders, $corsOrigin );
		}
		else
			throw new \Exception( "The '{$method}' method already has a registered handler" );
	}

	public final static function custom( $handlers ){
		$route = new Route( $request );
		foreach( $handlers as $method => $classMethod  ){
			$cb = function(){
				$reflectionMethod = new ReflectionMethod( $classMethod );
				return $reflectionMethod->invoke( $route );
			};

			$route->setHandler( $method, $cb,  );
		}
	}

	protected final function dump(){
		$this->text();
		call_user_func_array( 'dump', func_get_args() );
	}

	protected final function html(){
		if( $this->request->method === 'GET' || 
			$this->request->method === 'OPTIONS' ){
			$this->response->setContentType( 'html' );
			return $this;
		}
		else{
			if( !empty( $this->request->headers['content-type'] ) ){
				if( !$this->request->isContentType( 'text/html' ) )
					throw new \Exception( "Unsupported content type: {$this->request->headers['content-type']}", 422 );
			}
			else
				throw new \Exception( "Unspecified content type", 422 );


			$this->response->setContentType( 'text/html' );
			return $this;
		}
	}

	protected final function json(){
		if( $this->request->method === 'GET' ||
			$this->request->method === 'OPTIONS' ){
			$this->response->setContentType( 'json' );
			return $this;
		}
		else{
			if( !empty( $this->request->headers['content-type'] ) ){
				if( !$this->request->isContentType( 'application/json' ) )
					throw new \Exception( "Unsupported content type: {$this->request->headers['content-type']}", 422 );
			}
			else
				throw new \Exception( "Unspecified content type", 422 );


			$this->request->data = $this->request->getParseJSON();
			if( empty( $this->request->data ) )
				throw new \Exception( "Can't parse data", 422 );


			$this->response->setContentType( 'application/json' );
			return $this;
		}
	}

	protected final function text(){
		if( $this->request->method === 'GET' ||
			$this->request->method === 'OPTIONS' ){
			$this->response->setContentType( 'text' );
			return $this;
		}
		else{
			if( !empty( $this->request->headers['content-type'] ) ){
				if( !$this->request->isContentType( 'text/plain' ) )
					throw new \Exception( "Unsupported content type: {$this->request->headers['content-type']}", 422 );
			}
			else
				throw new \Exception( "Unspecified content type", 422 );


			$this->response->setContentType( 'text' );
			return $this;
		}
	}
}
