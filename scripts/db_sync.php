<?php
require( '../lib/config.php' );

$source = 'mongodb://192.168.1.31:27017/scintillator';
$dest = 'mongodb://192.168.1.31:27017/trash';
#$dest = 'mongodb+srv://scintillator_demo:ZJPvHi7UQ1Hb3gtC@cluster0.kvmcv.mongodb.net/scintillator?authSource=admin&replicaSet=atlas-10ao69-shard-0&w=majority&readPreference=primary&appname=MongoDB%20Compass&retryWrites=true&ssl=true';


$client_src = new \MongoDB\Client( $source );
$db_src = $client_src->selectDatabase( 'scintillator' );

$client_dest = new \MongoDB\Client( $dest );
$db_dest = $client_dest->selectDatabase( 'trash' );

$ts = date( 'Y-m-d_His' );
$path = __DIR__ . DS .'backups';
if( !is_dir( $path ) ){
	mkdir( $path );
}

$path .= DS ."{$ts}";
mkdir( $path );

foreach( $db_src->listCollections() as $col ){
	\Log::info( $col['name'] );
	$col_dest = $db_dest->selectCollection( $col['name'] );
	$count = $col_dest->countDocuments();
	if( $count ){
		$col_path = $path . DS ."{$col['name']}.json";
		$fp = fopen( $col_path, 'w' );
		fwrite( $fp, '[' );
		$res = $col_dest->find();
		foreach( $res as $i => $doc ){
			if( $i ){
				fwrite( $fp, ','. PHP_EOL . json_encode( $doc ) );
			}
			else{
				fwrite( $fp, json_encode( $doc ) );
			}
		}
		fwrite( $fp, ']' );
		fclose( $fp );
	}

	$col_dest->deleteMany(array());

	$col_src = $db_src->selectCollection( $col['name'] );
	$res = $col_src->find();
	foreach( $res as $doc ){
		try{
			$col_dest->insertOne( $doc->jsonSerialize() );
		}
		catch( Exception $ex ){
			\Log::warning( $ex->getMessage() );
			\Log::info( $doc );
		}
	}
}


