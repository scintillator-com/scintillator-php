<?php

namespace Controllers;

class Generator extends \Route{
	use \Traits\Authorized;

	public final function __construct( \Request $request ){
		parent::__construct( $request );
		$this->setHandler( 'GET', array( $this, 'GET' ), array( 'Accept', 'Authorization', 'Content-Type' ) );
	}

	/**
	 * NOTE: `protected` handlers can only be used by their own controllers
	 *   Use `public` to allow CustomController
	 **/
	public final function GET(){
		$this->json()->authorize();

		$this->required = array();
		
		$this->optional = array(
			'language' => array( 'format' => 'string' ),
			'library'  => array( 'format' => 'string' ),
			'name'     => array( 'format' => 'string' )
		);

		$data = $this->validate( $_GET );

		$generatorQuery = array(
			'is_enabled' => true
		);
		/*
		$generatorProgection = array(
			'projection' => \Models\Moment::getSummaryProjection()
		);
		*/
		foreach( $data as $key => $value ){
			if( !empty( $this->optional[ $key ][ 'dataKey' ] )){
				$k = $this->optional[ $key ][ 'dataKey' ];
				$generatorQuery[ $k ] = $value;
			}
			else{
				switch( $key ){
					case 'language':
					case 'library':
					case 'name':
						$generatorQuery[ $key ] = $value;
						break;

					/*
					case 'created-before':
						//$query['request.created'][ '$lte' ] = new \MongoDB\BSON\UTCDateTime
						break;
					*/

					//case 'host':
					//	$momentQuery[ 'request.host' ] = $data['host'];
					//	break;

					//case 'id':
					//	$momentQuery[ '_id' ] = new \MongoDB\BSON\ObjectId( $value );
					//	break;


					default:
						\Log::warning( "Unsupported parameter: {$key}" );
						break;
				}
			}
		}

		$responses = array();
		$res = $this->selectCollection( 'generators' )->find( $generatorQuery );
		foreach( $res as $generator ){
			$responses[] = \Views\Generator::default( $generator );
		}
		$this->response->print( $responses, 200 );
	}
}
