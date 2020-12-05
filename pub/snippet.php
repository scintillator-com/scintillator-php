<?php
if( empty( $_GET['id'] ) ){
	header( 'HTTP/1.1 404 Not Found', true, 404 );
	//TODO: something cute here
	echo 'Not Found';
	exit;
}

$start = microtime( true );
error_reporting( E_ALL & ~E_STRICT );
ini_set( 'display_errors', 0 );
date_default_timezone_set('UTC');

$lib = dirname( $_SERVER[ 'DOCUMENT_ROOT' ] ) . DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR;
define( 'LIB', $lib );
define( 'ROOT', $_SERVER[ 'DOCUMENT_ROOT' ] );

require_once( LIB .'config.php' );
require( '/home/lc/sites/scintillator-php/vendor/autoload.php' );

$query[ '_id' ] = new MongoDB\BSON\ObjectId( $_GET['id'] );
$config = Configuration::Load();
$client = new MongoDB\Client( $config->mongoDB['uri'] );
$db = $client->selectDatabase( 'scintillator' );
$snippet = $db->selectCollection( 'snippets' )->findOne( $query );
if( !$snippet ){
	header( 'HTTP/1.1 404 Not Found', true, 404 );
	//TODO: something cute here
	echo 'Not Found';
	exit;
}


$query['_id'] = $snippet->moment_id;
$options = array(
	'projection' => array(
		'request' => 1
	)
);
$moment = $db->selectCollection( 'moments' )->findOne( $query, $options );
if( !$moment ){
	header( 'HTTP/1.1 404 Not Found', true, 404 );
	//TODO: something cute here
	echo 'Not Found';
	exit;
}


$formatter = Snippet_Formatter::create( $snippet );
$code = $formatter->format( $moment );
?><!DOCTYPE html>
<html lang="en">
<head>
	<title></title>

	<meta http-equiv="Content-Language" content="en-us" />
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />

	<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.4.0/highlight.min.js"></script>
	<script charset="UTF-8" src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.4.0/languages/<?=$snippet->formatter->language;?>.min.js"></script>

	<!-- TODO: theme -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.4.0/styles/default.min.css" />

	<style type="text/css">
	.ns {
		margin:0;
		padding:0;
	}
	</style>
</head>
<body class="ns">
<pre class="ns"><code class="language-<?=$snippet->formatter->language;?>"><?=$code;?></code></pre>
<script language="JavaScript" type="text/javascript">
hljs.initHighlightingOnLoad()
<?php
/*
window.addEventListener( 'load', () => {
})

document.addEventListener( 'DOMContentLoaded', () => {
  document.querySelectorAll('pre code').forEach((block) => {
    //hljs.highlightBlock(block);
  });
});
*/
?>
</script>
</body>
</html>
<?php
Log::info('Duration: '.(microtime( true ) - $start));