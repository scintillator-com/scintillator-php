<?php

require( 'Request.php' );
require( 'Response.php' );
require( 'Route.php' );

final class Application{

	public $controllers = array();
	public $mode = 'files';

	private $libPath = null;
	private $request = null;
	private $route = null;
	private $start = null;

	private static $first = null;

	public function __construct( $libPath, $start=null ){
		$this->libPath = $libPath;
		$this->start = $start ? $start : hrtime( true );
		$this->runOnce();
	}

	public static final function explicit( $routeClass, $start=null ){
		$instance = new \Application( null, $start );
		if( $instance->loadRequest() ){
			$instance->route = new $routeClass( $instance->request );
			$instance->processRoute();
		}
		
		return $instance;
	}

	public final function loadRequest(){
		try{
			$this->request = \Request::Load();
\Log::info( "{$this->request->method} {$this->request->fullPath}" );
			return true;
		}
		catch( \Exception $ex ){
			$response = new \Response();
			$response->emitException( $ex );
			return false;
		}
	}

	public final function routeRequest(){
		$pieces = explode( '/', trim( $this->request->path, '/' ) );
		while( $pieces ){
			switch( $this->mode ){
				case 'db':
					throw new \Exception( "Not Implemented: Application::\$mode = 'db'" );

				case 'files':
					$abs_path = $this->libPath . DS . implode( DS, $pieces ) .'.php';
					if( file_exists( $path ) ){
						\Log::debug( "Application->\$mode 'files'" );
						require( $path );
						return $this;
					}
					break;

				case 'map':
					$rel_path = '/'. implode( '/', $pieces );
					if( $this->controllers->tryGet( $rel_path, $controller, $key ) ){
						\Log::debug( "Application->\$mode 'map'" );
						if( $key instanceof \RegexString && $key->test( $rel_path, $matches ) ){
							foreach( $matches as $k => $v ){
								if( !is_int( $k ) ){
									$this->request->urlArgs[] = $v;
									$this->request->namedArgs[ $k ] = $v;
								}
							}
						}

						$this->route = new $controller( $this->request, $controller );
						return $this;
					}
					break;
			}

			$piece = array_pop( $pieces );
			array_unshift( $this->request->urlArgs, $piece );
		}

		$response = new \Response();
		$response->emit( 'Not Found', 404 );
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
			if( !empty( \Configuration::Load()->isDeveloper ) ){
				if( defined( 'E_DEPRECATED' ) )
					set_error_handler( 'errors_as_exceptions', E_ALL & ~E_DEPRECATED );
				else
					set_error_handler( 'errors_as_exceptions', E_ALL );
			}
		}
	}
}
