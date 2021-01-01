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

		$this->required = array();
		
		//TODO: mine=1
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
		$data = $this->pageable( 10, 50 )->validate( $_GET );
		
		
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

		//TODO: apply org_id


		$responses = array();
		$res = $this->selectCollection( 'moments' )->find( $query, $options );
		foreach( $res as $moment ){
			$responses[] = \Models\Moment::formatSummary( $moment );
		}

		$this->response->emit( $responses, 200 );
	}
}
