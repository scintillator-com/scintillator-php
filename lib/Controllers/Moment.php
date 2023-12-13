<?php

namespace Controllers;

final class Moment extends \Route {
	use \Traits\Authorized;

	public final function __construct( \Request $request ){
		parent::__construct( $request );

		$this->setHandler( 'DELETE', array( $this, 'DELETE' ), array( 'Accept', 'Authorization', 'Content-Type' ) );
		$this->setHandler( 'GET',    array( $this, 'GET' ),    array( 'Accept', 'Authorization', 'Content-Type' ) );
		$this->setHandler( 'PATCH',  array( $this, 'PATCH' ),  array( 'Accept', 'Authorization', 'Content-Type' ) );
	}

	public final function DELETE(){
		$this->json()->authorize();
		if( empty( $this->request->urlArgs ) )
			throw new \Exception( "The 'id' URL parameter is required", 422 );

		if( count( $this->request->urlArgs ) !== 1 )
			throw new \Exception( "Too many URL arguments, expected 1", 422 );


		$this->required = array(
			'id' => array( 'format' => 'MongoDB::ObjectId' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = $data['id'];

		$momentCol = $this->selectCollection( 'moments' );
		$moment = $momentCol->findOne( $query );
		if( empty( $moment ) )
			throw new \Exception( "Moment not found", 400 );

		if( !empty( $moment->is_locked ) )
			throw new \Exception( "The record cannot be deleted while it is locked", 409 );


		$momentRes = $momentCol->deleteOne( $query );
		if( self::deletedOne( $momentRes ) ){
			$this->empty()->response->print( null, 204 );
		}
		else{
			$this->response->dump( $momentRes );
			return;
		}
	}

	public final function GET(){
		$this->json()->authorize();
		if( empty( $this->request->urlArgs ) )
			throw new \Exception( "The 'id' URL parameter is required", 422 );

		if( count( $this->request->urlArgs ) > 1 )
			throw new \Exception( "Too many URL arguments, expected 1", 422 );


		$this->required = array(
			'id' => array( 'format' => 'MongoDB::ObjectId' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = new \MongoDB\BSON\ObjectId( $data['id'] );

		$moment = $this->selectCollection( 'moments' )->findOne( $query );
		if( $moment ){
			$response = \Models\Moment::formatDetail( $moment );
			$this->response->print( $response, 200 );
		}
		else{
			throw new \Exception( "Moment not found", 400 );
		}
	}

	public final function PATCH(){
		$this->json()->authorize();
		if( empty( $this->request->urlArgs ) )
			throw new \Exception( "The 'id' URL parameter is required", 422 );

		if( count( $this->request->urlArgs ) > 2 )
			throw new \Exception( "Too many URL arguments, expected 2", 422 );


		$this->required = array(
			'id' => array( 'format' => 'MongoDB::ObjectId' )
		);

		$this->request->data['id'] = $this->request->urlArgs[0];
		if( count( $this->request->urlArgs ) == 2 ){
			$this->request->data['section'] = $this->request->urlArgs[1];
			$this->required[ 'section' ] = array( 'format' => 'enum', 'enum' => array( 'request', 'response' ) );
		}

		$data = $this->validate();
		$n = count( $this->request->urlArgs );
		if( $n === 1 ){
			$this->patchMoment();
		}
		else if( $n === 2 ){
			if( $data['section'] === 'request' ){
				$this->patchMomentRequest();
			}
			else if( $data['section'] === 'response' ){
				$this->patchMomentResponse();
			}
			else{
				throw new \Exception( "Unsupported section: {$section}", 422 );
			}
		}
	}

	protected final function patchMoment(){
		$this->optional = array(
			'is_locked' => array( 'format' => 'boolean' ),
			'visibility' => array( 'format' => 'enum', 'enum' => array( 'public', 'private' ) )
		);

		$data = $this->validate();
		$query['_id'] = $data['id'];
		unset( $data['id'] );

		foreach( $data as $attr => $value ){
			$update[ '$set' ][ "{$attr}" ] = $value;
		}

		$momentsCol = $this->selectCollection( 'moments' );
		$res = $momentsCol->updateOne( $query, $update );
		if( $res->getMatchedCount() === 0 )
			throw new \Exception( "Moment not found", 400 );

		
		$moment = $momentsCol->findOne( $query );
		$response = \Models\Moment::formatDetail( $moment );
		$this->response->print( $response, 200 );
	}

	protected final function patchMomentRequest(){
		throw new \Exception( 'Not implemented', 501 );

		$this->optional = array(
			'body'    => array( 'format' => 'object' ),
			'headers' => array( 'format' => 'object' ),
			'query'   => array( 'format' => 'object' )
		);

		$data = $this->validate();
		$this->response->dump( $data ); exit;

		$query['_id'] = $data['id'];
		$moment = $this->selectCollection( 'moments' )->findOne( $query );
		$this->response->dump( $moment ); exit;
		if( !empty( $moment->is_locked ) )
			throw new \Exception( "The record cannot be deleted while it is locked" );


		$moment = $momentsCol->findOne( $query );
		$response = \Models\Moment::formatDetail( $moment );
		$this->response->print( $response, 200 );
	}
}

