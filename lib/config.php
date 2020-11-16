<?php
//exit(0); //we are offline right now

if( !defined( 'CLI' ) )
	define( 'CLI', php_sapi_name() == 'cli' );

if( !defined( 'DS' ) )
	define( 'DS', DIRECTORY_SEPARATOR );

require_once( 'functions.php' );
require_once( 'autoload.php' );

