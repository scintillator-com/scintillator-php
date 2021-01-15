<?php

if( !function_exists( 'apache_request_headers' ) ){
function apache_request_headers(){
	static $headers;
	if( !isset( $headers ) ){
		$headers = array();
		foreach( $_SERVER as $k => $v ){
			if( strncmp( 'HTTP_', $k, 5 ) == 0 ){
				$pieces = explode( '_', substr( $k, 5 ) );
				$pieces = array_map( 'strtolower', $pieces );
				$pieces = array_map( 'ucfirst', $pieces );
				$key = implode( '-', $pieces );
				$headers[ $key ] = $v;
			}
		}

		if( empty( $headers[ 'Authorization' ] ) && !empty( $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ] ) ){
			$headers[ 'Authorization' ] = $_SERVER[ 'REDIRECT_HTTP_AUTHORIZATION' ];
		}

		if( empty( $headers[ 'Content-Type' ] ) && !empty( $_SERVER[ 'CONTENT_TYPE' ] ) ){
			$headers[ 'Content-Type' ] = $_SERVER[ 'CONTENT_TYPE' ];
		}
	}
	return $headers;
}
}

function dump(){
	ob_start();
	echo '<pre style="text-align: left;">'. PHP_EOL;

	$frames = debug_backtrace();
	if( !empty( $frames[ 0 ] ) ){
		$file = $frames[ 0 ];
	}

	if( !empty( $frames[ 1 ] ) ){
		$class = $frames[ 1 ];
	}

	if( !empty( $frames[ 2 ] ) ){
		$prev = $frames[ 2 ];
	}

	if( !empty( $class[ 'class' ] ) ){
		echo $class[ 'class' ] . $class[ 'type' ] . $class[ 'function' ] ."()". PHP_EOL;
	}else if( !empty( $class ) ){
		echo $class[ 'function' ] . PHP_EOL;
	}else{
		echo "(no class)". PHP_EOL;
	}

	echo $file[ 'file' ] .':'. $file[ 'line' ] . PHP_EOL;


	echo PHP_EOL;
	echo '<pre style="margin-left: 4em;">'. PHP_EOL;
	echo "\t<strong>Previous Frame:</strong>". PHP_EOL;

	if( !empty( $prev[ 'class' ] ) ){
		echo "\t". $prev[ 'class' ] . $prev[ 'type' ] . $prev[ 'function' ] ."()". PHP_EOL;
	}else if( !empty( $prev ) ){
		echo "\t". $prev[ 'function' ] . PHP_EOL;
	}else{
		echo "\t(no function)". PHP_EOL;
	}

	if( !empty( $class[ 'file' ] ) ){
		echo "\t". $class[ 'file' ];
		if( !empty( $class[ 'line' ] ) ){
			echo ':'. $class[ 'line' ];
		}
		echo PHP_EOL;
	}

	echo '</pre>'. PHP_EOL;
	echo PHP_EOL;
	echo '----------------------------------------'. PHP_EOL;
	echo '<hr />'. PHP_EOL;

	$args = func_get_args();
	foreach( $args as $arg ){
		ob_start();
		var_dump( $arg );
		echo ob_get_clean() . PHP_EOL;
	}

	echo '</pre>'. PHP_EOL;

	$output = ob_get_clean();
	if( true || CLI ){
		return strip_tags( $output );
	}
	else{
		return $output;
	}
}

function dump_js(){
	$response = array(
		'data'   => func_get_args(),
		'frames' => array()
	);

	$frames = debug_backtrace();
	for( $i = 0; $i < count( $frames ); $i++ ){
		$ff = $frames[ $i + 0 ];
		$cf = !empty( $frames[ $i + 1 ] ) ? $frames[ $i + 1 ] : null;

		$add[ 'file' ] = "{$ff[ 'file' ]}";
		$add[ 'line' ] = (int)$ff[ 'line' ];
		if( !empty( $cf[ 'class' ] ) ){
			$add[ 'method' ] = "{$cf[ 'class' ]}{$cf[ 'type' ]}{$cf[ 'function' ]}()";
		}else if( !empty( $cf[ 'function' ] ) ){
			$add[ 'method' ] = "{$cf[ 'function' ]}()";
		}else{
		}

		$response[ 'frames' ][] = $add;
	}

	if( defined( 'JSON_PRETTY_PRINT' ) )
		echo json_encode( $response, JSON_PRETTY_PRINT );
	else
		echo json_encode( $response );
}

function errors_as_exceptions( $errno, $errstr, $errfile, $errline, $errcontext ){
	error_log( "ERROR AT {$errfile}:{$errline} - {$errstr}" );
	throw new Exception( $errstr, $errno );
}

if( !function_exists( 'getallheaders' ) ){
function getallheaders(){
	return apache_request_headers();
}
}

function get_max_execution_time(){
	static $met;
	if( !isset( $met ) ){
		$met = (int)ini_get( 'max_execution_time' );
		if( $met === 0 ){
			$met = 5;
		}
	}
	return $met;
}

function getNamedArguments( $args ){
	$queryStr = implode( '&', $args );

	$arguments = array();
	parse_str( $queryStr, $arguments );
	return $arguments;
}

function is_numeric_array( &$array ){
	if( !is_array( $array ) )
		return false;

	//empty array is numeric, technically
	if( empty( $array ) )
		return true;

	if( !array_key_exists( 0, $array ) )
		return false;

	$n = count( $array ) - 1;
	if( !array_key_exists( $n, $array ) )
		return false;

	return true;
}

function parse_tuple_header( &$header ){
	$types = array();
	$tmp = preg_split( '/\s*,\s*/', $header );
	for( $i = 0; $i < count( $tmp ); $i++ ){
		$p = preg_split( '/\s*;\s*/', $tmp[ $i ] );
		$type = array_shift( $p );
		$o[ 'q' ] = 1.0;
		$o[ 'type' ] = $type;
		foreach( $p as $px ){
			list( $k, $v ) = explode( '=', $px );
			$v = unquote( $v );
			if( $k == 'q' ){
				$o[ $k ] = (float)$v;
			}else{
				$o[ $k ] = $v;
			}
		}

		$types[ $type ] = $o;
	}

	uasort( $types, 'sort_tuple_header' );
	return $types;
}

function sort_tuple_header( &$l, &$r ){
	if( $l[ 'q' ] > $r[ 'q' ] )
		return -1;

	if( $l[ 'q' ] < $r[ 'q' ] )
		return 1;

	$lstars = substr_count( $l[ 'type' ], '*' );
	$rstars = substr_count( $l[ 'type' ], '*' );
	if( $lstars < $rstars )
		return -1;

	if( $lstars > $rstars )
		return 1;

	return 0;
}

function unquote( &$v ){
	if( $v[0] == '"' && substr( $v, -1 ) == '"' )
		return substr( $v, 1, -1 );

	if( $v[0] == "'" && substr( $v, -1 ) == "'" )
		return substr( $v, 1, -1 );

	return $v;
}

if( !function_exists( 'sys_get_temp_dir' ) ){
function sys_get_temp_dir(){
	if( !empty( $_ENV[ 'TMP' ] ) ){
		return realpath( $_ENV[ 'TMP' ] );
	}

	if( !empty( $_ENV[ 'TMPDIR' ] ) ){
		return realpath( $_ENV[ 'TMPDIR' ] );
	}

	if( !empty( $_ENV[ 'TEMP' ] ) ){
		return realpath( $_ENV[ 'TEMP' ] );
	}

	$tempfile = tempnam( __FILE__, '' );
	if( file_exists( $tempfile ) ){
		unlink( $tempfile );
		return realpath( dirname( $tempfile ) );
	}

	return null; 
}
}

if( !function_exists( 'unique' ) ){
function unique( $data, $type = null ){
	$data = array_keys( array_flip( (array)$data ) );
	switch( $type ){
		case 'int':
		case 'integer':
			$data = array_map( 'intval', $data );
			break;
		case 'str':
		case 'string':
			$data = array_map( 'strval', $data );
			break;
	}
	return $data;
}
}

if( !function_exists( 'unix2iso8601' ) ){
function unix2iso8601( $unixTime = null ){
	if( isset( $unixTime ) ){
		return date( 'Y-m-d\TH:i:s\Z', $unixTime );
	}else{
		return date( 'Y-m-d\TH:i:s\Z' );
	}
}
}

