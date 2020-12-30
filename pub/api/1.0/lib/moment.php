<?php

final class moment extends Route {
	use \Authorized;
	use \Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors( 'GET', array( 'Accept,Authorization,Content-Type' ));
	}

	public final function GET(){
		$this->json()->authorize();
		if( empty( $this->request->urlArgs ) )
			throw new Exception( "The 'id' URL parameter is required", 422 );

		if( count( $this->request->urlArgs ) !== 1 )
			throw new Exception( "Too many URL arguments, expected 1", 422 );


		$this->required = array(
			'id' => array( 'format' => 'string' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = new MongoDB\BSON\ObjectId( $data['id'] );

		$res = $this->selectCollection( 'moments' )->find( $query );
		$moment = $this->selectCollection( 'moments' )->findOne( $query );
		$response = \Models\Moment::formatDetail( $moment );
		$this->response->emit( $response, 200 );
	}
}
