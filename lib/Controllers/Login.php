<?php

namespace Controllers;

class Login extends \Controllers\Session{
	//inherited
	//use \Mongo;

	public final function __construct( \Request $request ){
		parent::__construct( $request );

		//$this->response->cors( 'POST', array( 'Accept,Content-Type' ));
		$this->setHandler( 'POST', array( $this, 'POST' ), array( 'Accept', 'Content-Type' ) );
	}

	protected final function POST(){
		$this->json();

		$this->required = array(
			'username' => array( 'format' => 'string', 'scalar' ),
			'password' => array( 'format' => 'string', 'scalar' )
		);
		$this->optional = array();
		
		//Route
		$data = $this->validate();
		$user = $this->validateUser( $data );
		$this->validateOrg( $user );
		
		$query = array( '_id' => $user->getID() );
		$userResult = $this->selectCollection( 'users' )->updateOne( $query, \Models\User::onLogin( $user ) );
		self::updatedOne( $userResult );

		if( $this->reuseSession( $user, $session ) ){
			$this->response->print( \Models\Session::view( $session ) );
		}
		else{
			$this->createSession( $user, $session );
			$this->response->print( \Models\Session::view( $session ) );
		}
	}

	private final function validateOrg( \Models\User $user ){
		if( !empty( $user->org_id ) ){
			$query = array( '_id' => $user->org_id );
			$org = $this->selectCollection( 'orgs' )->findOne( $query );
			if( $org && $org->is_enabled )
				return $org;
			else
				throw new \Exception( 'Not Authorized', 401 );
		}
	}

	private final function validateUser( $data ){
		$query = array(
			'username' => $data['username']
		);
		$options = array(
			'limit' => 1
		);

		$user = $this->selectCollection( 'users' )->findOne( $query, $options );
		if( $user && $user->is_enabled ){
			if( password_verify( $data['password'], $user->hash ) ){
				return new \Models\User( $user );
			}
			else{
				throw new \Exception( 'Not Authorized', 401 );			
			}
		}
		else{
			throw new \Exception( 'Not Authorized', 401 );
		}
	}
}