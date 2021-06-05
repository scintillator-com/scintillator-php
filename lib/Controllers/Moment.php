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
			'id' => array( 'format' => 'string' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		$query[ '_id' ] = new \MongoDB\BSON\ObjectId( $data['id'] );

		//if( !empty( $moment->is_locked ) )
		//	throw new \Exception( "The record cannot be deleted while it is locked" )

		$momentRes = $this->selectCollection( 'moments' )->deleteOne( $query );
		if( !self::deletedOne( $momentRes ) ){
			\Log::warning( $momentRes );
		}

		$this->response->print( '', 204 );
	}

	public final function GET(){
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
		$query[ '_id' ] = new \MongoDB\BSON\ObjectId( $data['id'] );

		$moment = $this->selectCollection( 'moments' )->findOne( $query );
		$response = \Models\Moment::formatDetail( $moment );
		$this->response->print( $response, 200 );
	}

	public final function PATCH(){
		$this->json()->authorize();
		if( empty( $this->request->urlArgs ) )
			throw new \Exception( "The 'id' URL parameter is required", 422 );


		$momentID = $this->request->urlArgs[0];
		$n = count( $this->request->urlArgs );
		if( $n === 1 ){
			$this->patchMoment( $momentID );
		}
		else if( $n === 2 ){
			$attribute = $this->request->urlArgs[1];
			if( $attribute === 'body' ){
				$this->patchMomentBody( $momentID );
			}
			else{
				throw new \Exception( "Unsupported attribute: {$attribute}", 422 );
			}
		}
	}

	protected final function patchMoment( $momentID ){
		//TODO: is_locked
		throw new \Exception( 'Not Implemented', 501 );
	}

	protected final function patchMomentBody( $momentID ){
		$query[ '_id' ] = new \MongoDB\BSON\ObjectId( $momentID );

		$this->required = array();
		$this->optional = array(
			'$set'   => array( 'format' => 'any' ),
			'$unset' => array( 'format' => 'any' )
		);
		$data = $this->validate();
		

		//$momentRes = $this->selectCollection( 'moments' )->findOne( $query );

		$this->required = array(
			'id' => array( 'format' => 'string' ),
		);
		$_GET['id'] = $this->request->urlArgs[0];
		$data = $this->validate( $_GET );
		

		//if( !empty( $moment->is_locked ) )
		//	throw new \Exception( "The record cannot be deleted while it is locked" )

		$momentRes = $this->selectCollection( 'moments' )->deleteOne( $query );
		if( !self::deletedOne( $momentRes ) ){
			\Log::warning( $momentRes );
		}

		$this->response->print( '', 204 );
	}
}
