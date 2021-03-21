<?php
$start = hrtime( true );
define( 'AXIS', dirname( $_SERVER[ 'DOCUMENT_ROOT' ] ) );
define( 'LIB', AXIS . DIRECTORY_SEPARATOR .'lib' );
require_once( LIB . DIRECTORY_SEPARATOR .'config.php' );
Application::explicit( '\Controllers\SnippetWidgetController', $start );
exit;