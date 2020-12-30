<?php

class Log{
	const DEBUG    = 1;
	const INFO     = 2;
	const WARN     = 4;
	const WARNING  = 4;
	const ERR      = 8;
	const ERROR    = 8;
	const CRIT     = 16;
	const CRITICAL = 16;
	const ALL      = 31;

	public static $Level = 30; //default to INFO | WARN | ERROR | CRIT

	private static function _log(){
		$frames = debug_backtrace();

		array_shift( $frames );
		$f2 = array_shift( $frames );
		$file   = isset( $f2[ 'file' ] ) ? basename( $f2[ 'file' ] ) : '(unknown)';
		$line   = isset( $f2[ 'line' ] ) ? basename( $f2[ 'line' ] ) : '(unknown)';

		$args = func_get_args();
		$sev = array_shift( $args );
		switch( $sev ){
			case self::DEBUG: $sevStr = 'DEBUG  '; break;
			case self::INFO:  $sevStr = 'INFO   '; break;
			case self::WARN:  $sevStr = 'WARNING'; break;
			case self::ERROR: $sevStr = 'ERROR  '; break;
			default:          $sevStr = 'UNKNOWN'; break;
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
		if( self::$Level & self::DEBUG ){
			$args = func_get_args();
			array_unshift( $args, self::DEBUG );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }

    public static function error( $msg ){
		if( self::$Level & self::ERROR ){
			$args = func_get_args();
			array_unshift( $args, self::ERROR );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }

	public static function getLevel( $lvlStr, $default = null ){
		switch( $lvlStr ){
			case 'debug':
			case 'ok':
				return self::DEBUG;

			case 'info':
				return self::INFO;

			case 'warn':
			case 'warning':
				return self::WARN;

			case 'error':
				return self::ERROR;

			case 'critical':
				return self::CRIT;

			default:
				Log::error( "Unrecognized error level: {$lvlStr}" );
				return $default ? $default : self::INFO;
		}
	}

    public static function info( $msg ){
		if( self::$Level & self::INFO ){
			$args = func_get_args();
			array_unshift( $args, self::INFO );
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
		if( self::$Level & self::WARN ){
			$args = func_get_args();
			array_unshift( $args, self::WARN );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }

    public static function warning( $msg ){
		if( self::$Level & self::WARN ){
			$args = func_get_args();
			array_unshift( $args, self::WARN );
			call_user_func_array( array( 'Log', '_log' ), $args );
		}
    }
}

