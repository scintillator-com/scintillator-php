<?php

final class login extends Route {
	use \Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors( 'POST' );
	}

	public final function POST(){
		$this->json();

		$this->required = array(
			'username' => array( 'format' => 'string', 'scalar' ),
			'password' => array( 'format' => 'string', 'scalar' )
		);
		$this->optional = array();
		$data = $this->validate();

		$user = $this->validateUser( $data );
		$this->validateOrg( $user );
		$query = array( '_id' => $user->getID() );
		$this->selectCollection( 'users' )->updateOne( $query, \Models\User::onLogin( $user ) );

		if( $this->reuseSession( $user, $session ) ){
			$this->response->emit( \Models\Session::view( $session ) );
		}
		else{
			$this->createSession( $user, $session );
			$this->response->emit( \Models\Session::view( $session ) );
		}
	}

	private final function createSession( $user, &$session ){
		\Log::debug( "Creating new token for user" );
		$session = \Models\Session::create( $user );
		$result = $this->selectCollection( 'sessions' )->insertOne( $session );
		if( !( $result->isAcknowledged() && $result->getInsertedCount() === 1 ) ){
			\Log::warning( $result );
		}
		return true;
	}

	private final function reuseSession( $user, &$session ){
		$session = $this->selectCollection( 'sessions' )->findOne( \Models\Session::findByUser( $user ) );
		if( $session ){
			$session = new \Models\Session( $session );

			try{
				$session->validate();
				\Log::debug( "Reusing token for user" );
				return true;
			}
			catch( Exception $_ ){
				//fall through and create a new session
			}
		}
		
		return false;
	}

	private final function validateOrg( \Models\User $user ){
		if( !empty( $user->org_id ) ){
			$query = array( '_id' => $user->org_id );
			$org = $this->selectCollection( 'orgs' )->findOne( $query );
			if( $org && $org->enabled )
				return $org;
			else
				throw new Exception( 'Not Authorized', 401 );
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
		if( $user && $user->enabled ){
			if( password_verify( $data['password'], $user->hash ) )
				return new \Models\User( $user );
			else
				throw new Exception( 'Not Authorized', 401 );			
		}
		else{
			//waste some time
			password_verify( '1234567890123456', 'abcdefghijklmnop' );
			throw new Exception( 'Not Authorized', 401 );
		}
	}
}
