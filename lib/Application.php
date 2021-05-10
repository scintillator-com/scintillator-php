<?php

require( 'Request.php' );
require( 'Response.php' );
require( 'Route.php' );

final class Application{
	private static $first = null;
	
	private $libPath = null;
	private $request = null;
	private $route = null;
	private $start = null;

	public function __construct( $libPath, $start=null ){
		$this->libPath = $libPath;
		$this->start = $start ? $start : hrtime( true );
		$this->runOnce();
	}

	public static final function explicit( $routeClass, $start=null ){
		$instance = new Application( null, $start );
		if( $instance->loadRequest() ){
			$instance->route = new $routeClass( $instance->request );
			$instance->processRoute();
		}
		
		return $instance;
	}

	public final function loadRequest(){
		try{
			$this->request = Request::Load();
\Log::info( "{$this->request->method} {$this->request->fullPath}" );
			return true;
		}
		catch( Exception $ex ){
			$response = new \Response();
			$response->emitException( $ex );
			return false;
		}
	}

	public final function routeRequest(){
		$pieces = explode( '/', trim( $this->request->path, '/' ) );
		while( $pieces ){
			$path = $this->libPath . DS . implode( DS, $pieces ) .'.php';
			if( file_exists( $path ) ){
				require( $path );
				return $this;
			}
			else{
				$piece = array_pop( $pieces );
				array_unshift( $this->request->urlArgs, $piece );
			}
		}

		Response::Create404()->emit();
		exit;
	}
	
	public final function processRoute(){
		$this->route->process();
		//\Log::info( 'Duration: '.(microtime( true ) - self::$start));
		\Log::info( 'Duration: '.(hrtime( true ) - $this->start)/1000000000);
	}

	private final function runOnce(){
		if( !self::$first ){
			self::$first = $this;
			if( !empty( Configuration::Load()->isDeveloper ) ){
				if( defined( 'E_DEPRECATED' ) )
					set_error_handler( 'errors_as_exceptions', E_ALL & ~E_DEPRECATED );
				else
					set_error_handler( 'errors_as_exceptions', E_ALL );
			}
		}
	}
}
