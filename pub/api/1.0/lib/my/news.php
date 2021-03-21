<?php

final class my_news extends Route {
	use \Authorized;
	use \Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors( 'GET', array( 'Accept,Authorization,Content-Type' ));
	}

	public final function GET(){
		$this->json()->authorize();


			'org_id' => $this->session->org_id,
			'org_id' => $this->session->user_id,
		//TOOD: get previous login
		//count moments for this org after that date
			//count projects created aafter date

		$this->required = array(
			'id' => array( 'format' => 'string' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = new MongoDB\BSON\ObjectId( $data['id'] );

		$res = $this->selectCollection( 'moments' )->find( $query );
		$moment = $this->selectCollection( 'moments' )->findOne( $query );
		$response = \Models\Moment::formatDetail( $moment );
		$this->response->print( $response, 200 );
	}
}
