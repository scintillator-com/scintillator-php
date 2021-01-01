<?php

final class org extends Route{
	use \Authorized;
	use \Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors( 'POST', array( 'Accept,Content-Type' ));
	}

	public final function GET(){
		$this->json()->authorize();
		throw new Exception( "Not Implemented", 501 );
	}

	public final function POST(){
		$this->json()->authorize();
		$user = $this->getSessionUser();
		if( $user && $user->org_id )
			throw new Exception( 'Users may only belong to one org', 409 );


		$this->required = array(
			'name' => array( 'format' => 'string', 'length' => array( 1, 255 ), 'scalar' ),
			'plan' => array( 'format' => 'enum', 'enum' => array( 'free', 'basic' ), 'scalar' )
		);

		$this->optional = array(
			'enabled' => array( 'format' => 'boolean', 'default' => true, 'scalar' )
		);

		$data = $this->validate();
		$plan = $this->selectCollection( 'plans' )->findOne(array( 'name' => $data['plan'] ));
		if( !$plan )
			throw new Exception( "Plan not found: {$data['plan']}", 404 );

		if( !$plan->enabled )
			throw new Exception( "Plan disabled: {$data['plan']}", 412 );


		$org = new \Models\Org( $data );
		$org->client_key = base64_encode( random_bytes( 24 ) ); //32 base64 chars
		$org->created    = $org->modified = new MongoDB\BSON\UTCDateTime();
		$org->created_by = $user->getID();
		$org->admins     = array( $this->session->user_id );
		$org->users      = array( $this->session->user_id );
		$org->plan       = array(
			'_id'  => $plan->_id,
			'name' => $plan->name,
		);
		$org->validate();


		$orgResult = $this->_createOrg( $org, $orgResult );

		$plan = new \Models\Plan( $plan );
		$rateLimit = \Models\RateLimit::createForOrg( $org, $plan );
		$this->_createRateLimit( $rateLimit, $rlResult );

		$user->org_id = $orgResult->getInsertedId();
		$this->_updateUser( $user, $userResult );

		if( $newSession = $this->_updateSession( $user, $sessResult ) ){
			$session = $newSession;
		}

		$this->response->emit( array(
			'authorization' => \Models\Session::view( $session ),
			'org' => array(
				'client_key' => $org->client_key
			)
		), 201);
	}
	
	private final function _createOrg( \Models\Org $org, &$orgResult ){
		try{
			$orgResult = $this->selectCollection( 'orgs' )->insertOne( $org );
			\Log::debug( "Org created: {$org->name}" );
		}
		catch( \MongoDB\Driver\Exception\BulkWriteException $ex ){
			if( $ex->getCode() === 11000 ){
				\Log::warning( $ex->getMessage() );
				throw new Exception( "Org already exists", 409, $ex );
			}
			else{
				\Log::error( "{$ex}" );
				throw new Exception( 'Internal Server Error', 500, $ex );
			}
		}
		catch( Exception $ex ){
			\Log::error( "{$ex}" );
			throw new Exception( 'Internal Server Error', 500, $ex );
		}

		if( self::insertedOne( $orgResult ) ){
			$org->setID( $orgResult );
			return $orgResult;
		}
		else
			throw new Exception( 'Internal Server Error', 500 );
	}

	private final function _createRateLimit( \Models\RateLimit $rateLimit, &$rlResult ){
		try{
			$rlResult = $this->selectCollection( 'rate_limits' )->insertOne( $rateLimit );
			\Log::debug( "RateLimit created: {$rateLimit->org_client_key}" );
		}
		catch( \MongoDB\Driver\Exception\BulkWriteException $ex ){
			if( $ex->getCode() === 11000 ){
				\Log::warning( $ex->getMessage() );
				throw new Exception( "Rate-limit already exists", 409, $ex );
			}
			else{
				\Log::error( "{$ex}" );
				throw new Exception( 'Internal Server Error', 500, $ex );
			}
		}
		catch( Exception $ex ){
			\Log::error( "{$ex}" );
			throw new Exception( 'Internal Server Error', 500, $ex );
		}

		if( self::insertedOne( $rlResult ) ){
			return $rlResult;
		}
		else
			throw new Exception( 'Internal Server Error', 500 );
	}

	private final function _updateSession( \Models\User $user, &$sessResult ){
		$session = \Models\Session::createForUser( $user )->validate();

		$query = array( '_id' => $this->session->getID() );
		$update = array(
			'$set' => array(
				'created' => $session->created,
				'expires' => $session->expires,
				'org_id'  => $session->org_id
			)
		);
		$sessResult = $this->selectCollection( 'sessions' )->updateOne( $query, $update );
		if( self::updatedOne( $sessResult ) ){
			\Log::debug( "Session updated: {$this->session->getID()}" );
			$this->session->created = $session->created;
			$this->session->expires = $session->expires;
			$this->session->org_id  = $session->org_id;
			return $this->session;
		}
		else{
			\Log::warning( $sessResult );
			return null;
		}
	}

	private final function _updateUser( \Models\User $user, &$userResult ){
		$query  = array( '_id'  => $user->getID() );
		$update = array( '$set' => array( 'org_id' => $user->org_id ));
		$userResult = $this->selectCollection( 'users' )->updateOne($query, $update);
		if( self::updatedOne( $userResult ) ){
			\Log::debug( "User updated: {$user->username}" );
			return $userResult;
		}
		else{
			\Log::warning( $userResult );
			return null;
		}
	}
}