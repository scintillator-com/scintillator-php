<?php
final class Configuration{
	private static $required = array(
		'MONGO_DB',
		'MONGO_URI',
		'SESSION_LIMITS'
	);

	private final function __construct(){
		foreach( self::$required as $param ){
			if( empty( $_ENV[ $param ] ) ){
				\Log::error( "Missing application (ENV) configuration parameter: '{$param}'" );
				throw new \Exception( 'Application not configured', 500 );
			}
		}
		
		$this->isDeveloper = false;
		if( !empty( $_ENV['IS_DEV'] ) && (int)$_ENV['IS_DEV'] ){
			$this->isDeveloper = true;
		}

		$this->mongoDB = array(
			'database' => $_ENV['MONGO_DB'],
			'uri'      => $_ENV['MONGO_URI']
		);

		$limits = json_decode( $_ENV['SESSION_LIMITS'] );
		$this->session = array(
			//  3600s = 1 hour
			'duration_default' => $limits[0],
			// 86400s = 1 day
			'duration_max'     => $limits[1],
			//   300s = 5 min
			'duration_short'   => $limits[2],
			//   300s = 5 min
			'sunset'           => $limits[3]
		);
	}

	public final function generateClientKey(){
		return base64_encode( random_bytes( 24 ) ) .'/tor'; //36 base64 chars
	}

	public final function get( $key, $default = null ){
		if( isset( $this->{$key} ) ){
			//Log::info( "Sending configuration attribute: {$key} = {$this->{$key}}" );
			return $this->{$key};
		}
		else if( isset( $default ) ){
			//Log::info( "Undefined configuration attribute: {$key}.  Sending default value: {$default}" );
			return $default;
		}
		else{
			throw new \Exception( "Undefined configuration attibute: {$key}" );
		}
	}

	public static final function load( $hostNames=array() ){
		static $config;
		if( !empty( $config ) )
			return $config;


		$config = new \Configuration();
		//$start = hrtime( true );

		/*
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
		
		foreach( $hostNames as &$hostName ){
			$hostConfigPath = "{$basePath}{$hostName}.php";
			if( file_exists( $hostConfigPath ) ){
				$config->_require( $hostConfigPath );
				break;
			}
		}
		*/

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

