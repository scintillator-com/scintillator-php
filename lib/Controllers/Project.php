<?php

namespace Controllers;

final class Project extends \Route {
	use \Traits\Authorized;

	public final function __construct( \Request $request ){
		parent::__construct( $request );

		$this->setHandler( 'GET',   array( $this, 'GET' ),   array( 'Accept', 'Authorization', 'Content-Type' ) );
		$this->setHandler( 'PATCH', array( $this, 'PATCH' ), array( 'Accept', 'Authorization', 'Content-Type' ) );
	}

	public final function GET(){
		$this->json()->authorize();

		$this->required = array();
		$this->optional = array(
			'created-after'  => array( 'format' => 'iso8601' ),
			'created-before' => array( 'format' => 'iso8601' ),
			'id'   => array( 'format' => 'string' ),
			'host' => array( 'format' => 'string', 'dataKey' => 'host' ),
			'sort' => array( 'format' => 'string', 'default' => 'created' )
		);
		$data = $this->pageable( 5, 10 )->validate( $_GET );

		$query = array();
		$options = array();
		foreach( $data as $key => $value ){
			if( !empty( $this->optional[ $key ][ 'dataKey' ] )){
				$k = $this->optional[ $key ][ 'dataKey' ];
				$query[ $k ] = $value;
			}
			else{
				switch( $key ){
					/*
					case 'created-after':
						//$query['request.created'][ '$gte' ] = new \MongoDB\BSON\UTCDateTime
						break;

					case 'created-before':
						//$query['request.created'][ '$lte' ] = new \MongoDB\BSON\UTCDateTime
						break;
					*/

					case 'id':
						$query[ '_id' ] = new \MongoDB\BSON\ObjectId( $value );
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
						\Log::warning( "Unsupported parameter: {$key}" );
						break;
				}
			}
		}

		$query['org_id'] = $this->session->org_id;

		$responses = array();
		$res = $this->selectCollection( 'projects' )->find( $query, $options );
		foreach( $res as $project ){
			$responses[] = \Models\Project::formatSummary( $project );
		}

		$this->response->print( $responses, 200 );
	}

	protected final function PATCH(){
		$this->json()->authorize();

		$this->required = array(
			'host' => array( 'format' => 'string' )
		);
		$this->optional = array();
		$data = $this->validate();


		$projectQuery = array(
			'host'   => $data['host'],
			'org_id' => $this->session->org_id
		);
		$project = $this->selectCollection( 'projects' )->findOne( $projectQuery );
		if( !$project )
			throw new \Exception( "Project not found: {$data['host']}", 404 );


		$rlQuery = array(
			'org_id' => $this->session->org_id
		);
		$rate_limit = $this->selectCollection( 'rate_limits' )->findOne( $rlQuery );
		if( !$rate_limit )
			throw new \Exception( "Customer profile missing", 409 );


		if( $rate_limit->projects_adhoc ){
			//critical portion, remove 1 project and unlock the current one
			$rlUpdate = array(
				'$inc' => array(
					'projects_adhoc' => -1
				),
				'$currentDate' => array(
					'modified' => true
				)
			);
			$res = $this->selectCollection( 'rate_limits' )->updateOne( $rlQuery, $rlUpdate );

			$projectUpdate = array(
				'$set' => array(
					'is_locked' => false
				),
				'$currentDate' => array(
					'modified' => true
				)
			);
			$res = $this->selectCollection( 'projects' )->updateOne( $projectQuery, $projectUpdate );

			$project->is_locked = false;
		}
		else{
			//402 Payment Required: please upgrade or prompt for purchase 
			throw new \Exception( "Payment Required", 402 );
		}

		$response = \Models\Project::formatSummary( $project );
		$this->response->print( $response, 201 );
	}
}
