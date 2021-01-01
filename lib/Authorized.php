<?php
trait Authorized{
	use \Mongo;

	protected $session = null;

	protected final function authorize(){
		if( empty( $this->request->headers['authorization'] ) )
			throw new Exception( 'Not Authorized', 401 );

		$token = $this->request->headers['authorization'];
		if( strncasecmp( $token, 'bearer', 6 ) === 0 )
			$token = trim( substr( $token, 6 ) );

		$session = $this->selectCollection( 'sessions' )->findOne( \Models\Session::findByToken( $token ) );
		if( $session ){
			$this->session = new \Models\Session( $session );
			$this->validateSession( $token );
			$this->validateUser( $token );
			if( $this->session->org_id )
				$this->validateOrg( $token );

			//TODO:
			//$this->rateLimit();
		}
		else{
			throw new Exception( "Session Invalid", 401 );
		}

		return $this;
	}

	protected final function getSessionOrg(){
		static $org = null;
		if( !empty( $this->session->org_id ) && empty( $org ) ){
			$org = $this->selectCollection( 'orgs' )->findOne(array( '_id' => $this->session->org_id ));
			if( $org )
				$org = new \Models\Org( $org );
		}
		return $org;
	}

	protected final function getSessionUser(){
		static $user = null;
		if( $this->session && empty( $user ) ){
			$user = $this->selectCollection( 'users' )->findOne(array( '_id' => $this->session->user_id ));
			if( $user )
				$user = new \Models\User( $user );
		}
		return $user;
	}

	private final function validateOrg( $token ){
		$org = $this->getSessionOrg();
		if( $org ){
			if( $org->enabled ){
				//ok
			}
			else{
				\Log::warning( "Disabled org: {$token}" );
				throw new Exception( "Session Invalid", 401 );
			}
		}
		else{
			\Log::error( "No org: {$token}" );
			throw new Exception( "Session Invalid", 401 );
		}
	}

	private final function validateSession( $token ){
		if( !$this->session->enabled ){
			\Log::warning( "Disabled session: {$token}" );
			throw new Exception( "Session Invalid", 401 );
		}

		if( !$this->session->isStarted() ){
			\Log::warning( "Future session: {$token}" );
			throw new Exception( "Session Invalid", 401 );
		}

		if( $this->session->isExpired() )
			throw new Exception( "Session Invalid", 401 );

		if( !$this->session->isValidDuration() ){
			\Log::warning( "Session to long: {$token}" );			
			throw new Exception( "Session Invalid", 401 );
		}
	}

	private final function validateUser( $token ){
		$user = $this->getSessionUser();
		if( $user ){
			if( $user->enabled ){
				//ok
			}
			else{
				\Log::warning( "Disabled user: {$token}" );
				throw new Exception( "Session Invalid", 401 );
			}
		}
		else{
			\Log::error( "No user: {$token}" );
			throw new Exception( "Session Invalid", 401 );
		}
	}
}
