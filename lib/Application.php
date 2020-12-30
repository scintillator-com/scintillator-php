<?php

final class Application{
	private static $libPath = null;
	private static $request = null;
	private static $route = null;
	private static $start = null;

	public static final function init( $libPath, $start ){
		self::$libPath = $libPath;
		self::$start = $start;

		//define( 'ROOT', $_SERVER[ 'DOCUMENT_ROOT' ] );

		$config = Configuration::Load();
		if( !empty( $config->isDeveloper ) ){
			if( defined( 'E_DEPRECATED' ) ){
				set_error_handler( 'errors_as_exceptions', E_ALL & ~E_DEPRECATED );
			}else{
				set_error_handler( 'errors_as_exceptions', E_ALL );
			}
		}
	}

	public static final function loadRequest(){
		try{
			self::$request = Request::Load();
			return true;
		}
		catch( Exception $ex ){
			$response = new \Response();
			$response->emitException( $ex );
			return false;
		}
	}

	public static final function routeRequest(){
		$request = self::$request;
		$pieces = explode( '/', trim( $request->path, '/' ) );
		while( $pieces ){
			$path = self::$libPath . DS . implode( DS, $pieces ) .'.php';
			if( file_exists( $path ) ){
				$nClassesBefore = count(get_declared_classes());

				require( $path );
				$lastClass = implode( '_', $pieces );
				if( !class_exists( $lastClass ) ){
					\Log::warning( "API class not found: {$lastClass}, attempting to use most recent" );
					$classes = get_declared_classes();
					array_splice( $classes, 0, $nClassesBefore );

					$lastClass = array_pop( $classes );
					\Log::warning( "Most recent class: {$lastClass}" );
				}

				self::$route = new $lastClass( $request );
				return;
			}
			else{
				$piece = array_pop( $pieces );
				array_unshift( $request->urlArgs, $piece );
			}
		}

		Response::Create404()->emit();
		exit;
	}
	
	public static final function processRoute(){
		self::$route->process();
		//\Log::info( 'Duration: '.(microtime( true ) - self::$start));
		\Log::info( 'Duration: '.(hrtime( true ) - self::$start)/1000000000);
	}
}
