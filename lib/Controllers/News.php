<?php

namespace Controllers;

class News extends \Route{
	use \Traits\Authorized;
	use \Traits\Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
    $this->setHandler( 'GET', array( $this, 'GET' ), array( 'Accept', 'Authorization', 'Content-Type' ) );
	}

  /**
	 * NOTE: `protected` handlers can only be used by their own controllers
	 *   Use `public` to allow CustomController
	 **/
	protected final function GET(){
		$this->json()->authorize();


		//	'org_id' => $this->session->org_id,
		//	'org_id' => $this->session->user_id,
		//TOOD: get previous login
		//count moments for this org after that date
		//count projects created aafter date

		$this->required = array(
			'id' => array( 'format' => 'string' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = new \MongoDB\BSON\ObjectId( $data['id'] );

		$res = $this->selectCollection( 'moments' )->find( $query );
		$moment = $this->selectCollection( 'moments' )->findOne( $query );
		$response = \Models\Moment::formatDetail( $moment );
		$this->response->print( $response, 200 );
	}
}
