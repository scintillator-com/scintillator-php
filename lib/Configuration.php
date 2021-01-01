<?php
final class Configuration{
	private final function __construct(){}

	public final function Get( $key, $default = null ){
		if( isset( $this->{$key} ) ){
			//Log::info( "Sending configuration attribute: {$key} = {$this->{$key}}" );
			return $this->{$key};
		}
		else if( isset( $default ) ){
			//Log::info( "Undefined configuration attribute: {$key}.  Sending default value: {$default}" );
			return $default;
		}
		else{
			throw new Exception( "Undefined configuration attibute: {$key}" );
		}
	}

	public static final function Load( $hostNames=array() ){
		static $config;
		if( !empty( $config ) )
			return $config;


		$config = new Configuration();
		//$configPath = LIB . DS .'configs'. DS .'default.php';
		//if( file_exists( $configPath ) ){
		//	$config->_include( $configPath );
		//}

		if( is_scalar( $hostNames ) )
			$hostNames = (array)$hostNames;

		$hostNames[] = gethostname();
		//$hostNames[] = php_uname('n');
		if( !empty( $_SERVER[ 'HTTP_HOST' ] ) )
			$hostNames[] = $_SERVER[ 'HTTP_HOST' ];

		$basePath = LIB . DS .'configs'. DS;
		//$start = hrtime( true );
		foreach( $hostNames as &$hostName ){
			$hostConfigPath = "{$basePath}{$hostName}.php";
			if( file_exists( $hostConfigPath ) ){
				$config->_require( $hostConfigPath );
				break;
			}
		}
		//\Log::info( 'Config load: '. (hrtime( true ) - $start) .'ns');
		return $config;
	}

	private final function _include( $path ){
		include( $path );
	}

	private final function _require( $path ){
		require( $path );
	}
}

