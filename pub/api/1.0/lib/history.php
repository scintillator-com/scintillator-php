<?php

final class history extends Route {
	use \Authorized;
	use \Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors( 'GET', array( 'Accept,Authorization,Content-Type' ));
	}

	public final function GET(){
		$this->json()->authorize();

		//TODO: list/search vs getDetail


		$this->required = array(
			'host' => array( 'format' => 'string', 'dataKey' => 'request.host' ),
		);

		$this->optional = array(
			//request
			'created-after'  => array( 'format' => 'iso8601' ),
			'created-before' => array( 'format' => 'iso8601' ),

			'id' => array( 'format' => 'string' ),

			'method' => array( 'format' => 'string', 'dataKey' => 'request.method'  ),
			'path'   => array( 'format' => 'string', 'dataKey' => 'request.path'    ),
			'scheme' => array( 'format' => 'string', 'dataKey' => 'request.scheme'  ),

			//response
			'status_code' => array( 'format' => 'string', 'dataKey' => 'response.status_code' ),
			
			//
			'sort' => array( 'format' => 'string', 'default' => 'request.created' )
		);
		$data = $this->pageable( 10, 50 )->validate( $_GET );


		//check this project is unlocked
		$projectQuery = array(
			'org_id' => $this->session->org_id,
			'host'   => $data['host']
		);
		$project = $this->selectCollection( 'projects' )->findOne( $projectQuery );
		if( !$project )
			throw new Exception( "Project not found: {$data['host']}", 404 );

		if( $project->is_locked ){
			//402 Payment Required: please upgrade or prompt for purchase 
			throw new Exception( "Payment Required", 402 );
		}


		$momentQuery = array();
		$momentOptions = array(
			'projection' => \Models\Moment::getSummaryProjection()
		);
		foreach( $data as $key => $value ){
			if( !empty( $this->optional[ $key ][ 'dataKey' ] )){
				$k = $this->optional[ $key ][ 'dataKey' ];
				$momentQuery[ $k ] = $value;
			}
			else{
				switch( $key ){
					/*
					case 'created-after':
						//$query['request.created'][ '$gte' ] = new MongoDB\BSON\UTCDateTime
						break;

					case 'created-before':
						//$query['request.created'][ '$lte' ] = new MongoDB\BSON\UTCDateTime
						break;
					*/

					case 'host':
						$momentQuery[ 'request.host' ] = $data['host'];
						break;

					case 'id':
						$momentQuery[ '_id' ] = new MongoDB\BSON\ObjectId( $value );
						break;

					case 'page':
						$momentOptions[ 'skip' ]  = ( $data['page'] - 1 ) * $data['pageSize'];
						break;

					case 'pageSize':
						$momentOptions[ 'limit' ] = $value;
						break;

					case 'sort':
						$momentOptions[ 'sort' ] = array( $value => -1 );
						break;

					default:
						Log::warning( "Unsupported parameter: {$key}" );
						break;
				}
			}
		}

		$momentQuery['org_id'] = $this->session->org_id;

		$responses = array();
		$res = $this->selectCollection( 'moments' )->find( $momentQuery, $momentOptions );
		foreach( $res as $moment ){
			$responses[] = \Models\Moment::formatSummary( $moment );
		}

		$this->response->emit( $responses, 200 );
	}
}
