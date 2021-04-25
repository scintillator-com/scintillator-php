<?php

namespace Controllers;

final class Moment extends \Route {
	use \Traits\Authorized;

	public final function __construct( \Request $request ){
		parent::__construct( $request );

		$this->setHandler( 'DELETE', array( $this, 'DELETE' ), array( 'Accept', 'Authorization', 'Content-Type' ) );
		$this->setHandler( 'GET',    array( $this, 'GET' ),    array( 'Accept', 'Authorization', 'Content-Type' ) );
	}

	protected final function DELETE(){
		$this->json()->authorize();
		if( empty( $this->request->urlArgs ) )
			throw new \Exception( "The 'id' URL parameter is required", 422 );

		if( count( $this->request->urlArgs ) !== 1 )
			throw new \Exception( "Too many URL arguments, expected 1", 422 );


		$this->required = array(
			'id' => array( 'format' => 'string' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = new MongoDB\BSON\ObjectId( $data['id'] );

		$moment = $this->selectCollection( 'moments' )->deleteOne( $query );
	}

	protected final function GET(){
		$this->json()->authorize();
		if( empty( $this->request->urlArgs ) )
			throw new \Exception( "The 'id' URL parameter is required", 422 );

		if( count( $this->request->urlArgs ) !== 1 )
			throw new \Exception( "Too many URL arguments, expected 1", 422 );


		$this->required = array(
			'id' => array( 'format' => 'string' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = new MongoDB\BSON\ObjectId( $data['id'] );

		$moment = $this->selectCollection( 'moments' )->findOne( $query );
		$response = \Models\Moment::formatDetail( $moment );
		$this->response->print( $response, 200 );
	}
}
