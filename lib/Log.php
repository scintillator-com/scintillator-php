<?php

if( !defined( 'IP_DEBUG' ) ){
	define( 'IP_DEBUG', 1 );
}

if( !defined( 'IP_INFO' ) ){
	define( 'IP_INFO', 2 );
}

if( !defined( 'IP_WARN' ) ){
	define( 'IP_WARN', 4 );
}

if( !defined( 'IP_WARNING' ) ){
	define( 'IP_WARNING', 4 );
}

if( !defined( 'IP_ERROR' ) ){
    define( 'IP_ERROR', 8 );
}

if( !defined( 'IP_CRIT' ) ){
    define( 'IP_CRIT', 16 );
}

if( !defined( 'IP_CRITICAL' ) ){
    define( 'IP_CRITICAL', 16 );
}

if( !defined( 'IP_ERROR_ALL' ) ){
	define( 'IP_ERROR_ALL', IP_DEBUG | IP_INFO | IP_WARN | IP_ERROR | IP_CRIT );
}

class Log{

	public static $Level = 30; //default to IP_INFO | IP_WARN | IP_ERROR | IP_CRIT

	private static function _log(){
		$frames = debug_backtrace();

		array_shift( $frames );
		$f2 = array_shift( $frames );
		$file   = isset( $f2[ 'file' ] ) ? basename( $f2[ 'file' ] ) : '(unknown)';
		$line   = isset( $f2[ 'line' ] ) ? basename( $f2[ 'line' ] ) : '(unknown)';

		$args = func_get_args();
		$sev = array_shift( $args );
		switch( $sev ){
			case IP_DEBUG: $sevStr = 'DEBUG  '; break;
			case IP_INFO:  $sevStr = 'INFO   '; break;
			case IP_WARN:  $sevStr = 'WARNING'; break;
			case IP_ERROR: $sevStr = 'ERROR  '; break;
			default:       $sevStr = 'UNKNOWN'; break;
		}

		$msg = array_shift( $args );
		if( is_scalar( $msg ) ){
			if( $args ){
				error_log( sprintf( "%s %s(%d) %s", $sevStr, $file, $line, vsprintf( "{$msg}", $args ) ) );
			}
			else{
				error_log( sprintf( "%s %s(%d) %s", $sevStr, $file, $line, "{$msg}" ) );
			}
		}
		else if( $args ){
			array_unshift( $args, $msg );
			$msg = json_encode( $args );
			error_log( sprintf( "%s %s(%d) %s", $sevStr, $file, $line, "{$msg}" ) );
		}
		else{
			$msg = json_encode( $msg );
			error_log( sprintf( "%s %s(%d) %s", $sevStr, $file, $line, "{$msg}" ) );
		}
	}

    public static function debug( $msg ){
		if( self::$Level & IP_DEBUG ){
			$args = func_get_args();
			array_unshift( $args, IP_DEBUG );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }

    public static function error( $msg ){
		if( self::$Level & IP_ERROR ){
			$args = func_get_args();
			array_unshift( $args, IP_ERROR );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }

	public static function getLevel( $lvlStr, $default = null ){
		switch( $lvlStr ){
			case 'debug':
			case 'ok':
				return IP_DEBUG;

			case 'info':
				return IP_INFO;

			case 'warn':
			case 'warning':
				return IP_WARN;

			case 'error':
				return IP_ERROR;

			case 'critical':
				return IP_CRIT;

			default:
				Log::error( "Unrecognized error level: {$lvlStr}" );
				return $default ? $default : IP_INFO;
		}
	}

    public static function info( $msg ){
		if( self::$Level & IP_INFO ){
			$args = func_get_args();
			array_unshift( $args, IP_INFO );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }

    public static function record( $level, $msg ){
		if( self::$Level & $level ){
			$args = func_get_args();
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }

    public static function warn( $msg ){
		if( self::$Level & IP_WARN ){
			$args = func_get_args();
			array_unshift( $args, IP_WARN );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }

    public static function warning( $msg ){
		if( self::$Level & IP_WARN ){
			$args = func_get_args();
			array_unshift( $args, IP_WARN );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }
}

