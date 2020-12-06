<?php

use \Models;

final class history extends Route {
	protected $formats = array(
		'application/json'
	);

	//public final function __construct( Request $request ){
	//	parent::__construct( $request );
	//}

	public final function GET(){
		$this->response->formatter = new Formatter_JSON();
		//$this->response->headers[ 'Access-Control-Allow-Credentials' ] = 'true';
		$this->response->headers[ 'Access-Control-Allow-Origin' ] = '*';
		if( !empty( $_SERVER['HTTP_ORIGIN'] ) )
			$this->response->headers[ 'Access-Control-Allow-Origin' ] = $_SERVER['HTTP_ORIGIN'];


		$this->required = array();
		$this->optional = array(
			//request
			'created-after'  => array( 'format' => 'iso8601' ),
			'created-before' => array( 'format' => 'iso8601' ),

			'id' => array( 'format' => 'string' ),

			'domain' => array( 'format' => 'string', 'dataKey' => 'request.host'    ),
			'host'   => array( 'format' => 'string', 'dataKey' => 'request.host'    ),
			'method' => array( 'format' => 'string', 'dataKey' => 'request.method'  ),
			'path'   => array( 'format' => 'string', 'dataKey' => 'request.path'    ),
			'scheme' => array( 'format' => 'string', 'dataKey' => 'request.scheme'  ),

			//response
			'status_code' => array( 'format' => 'string', 'dataKey' => 'response.status_code' ),
			
			//
			'sort' => array( 'format' => 'string', 'default' => 'request.created' )
		);
		$data = $this->pageable( 1, 50 )->validate( $_GET );
		
		
		$query = array();
		$options = array(
			'projection' => \Models\Moment::getSummaryProjection()
		);
		foreach( $data as $key => $value ){
			if( !empty( $this->optional[ $key ][ 'dataKey' ] )){
				$k = $this->optional[ $key ][ 'dataKey' ];
				$query[ $k ] = $value;
			}
			else{
				switch( $key ){
					case 'created-after':
						//$query['request.created'][ '$gte' ] = new MongoDB\BSON\UTCDateTime
						break;

					case 'created-before':
						//$query['request.created'][ '$lte' ] = new MongoDB\BSON\UTCDateTime
						break;

					case 'id':
						$query[ '_id' ] = new MongoDB\BSON\ObjectId( $value );
						break;

					case 'page':
						$options[ 'skip' ]  = ( $data['page'] - 1 ) * $data['pageSize'];
						break;

					case 'pageSize':
						$options[ 'limit' ] = $value;
						break;

					case 'sort':
						$options[ 'sort' ] = array( $value => -1 );
						break;

					default:
						Log::warning( "Unsupported parameter: {$key}" );
						break;
				}
			}
		}

		$responses = array();
		$config = Configuration::Load();
		$client = new MongoDB\Client( $config->mongoDB['uri'] );
		$res = $client->selectDatabase( 'scintillator' )->selectCollection( 'moments' )->find( $query, $options );
		foreach( $res as $moment ){
			$responses[] = \Models\Moment::formatSummary( $moment );
		}

		return $responses;
	}

	public final function OPTIONS(){
		header( 'HTTP/1.1 204 No Content', true, 204 );
		//header( 'Access-Control-Allow-Credentials: true');
		//header( 'Access-Control-Allow-Headers: Content-Type' );
		header( 'Access-Control-Allow-Methods: GET,HEAD' );
		
		if( !empty( $_SERVER['HTTP_ORIGIN'] ) )
			header( "Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}" );
		else
			header( "Access-Control-Allow-Origin: *" );
		exit;

		//$this->response->formatter = new Formatter_None();
		//$this->response->headers[ 'Access-Control-Allow-Credentials' ] = 'true';
		//$this->response->headers[ 'Access-Control-Allow-Headers' ] = 'Content-Type';
		//$this->response->headers[ 'Access-Control-Allow-Methods' ] = 'GET,HEAD';
		//$this->response->headers[ 'Access-Control-Allow-Origin' ] = $_SERVER['HTTP_ORIGIN'];
	}

}
