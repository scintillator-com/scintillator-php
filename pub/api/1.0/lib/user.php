<?php

final class user extends Route{
	use \Authorized;
	use \Mongo;
	
	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors( 'POST', array( 'Accept,Content-Type' ));
	}

	public final function POST(){
		$this->json();

		$creator = null;
		if( !empty( $this->request->headers['authorization'] ) ){
			$creator = $this->authorize()->getSessionUser();
		}

		$this->required = array(
			'email'      => array( 'format' => 'string', 'length' => array( 6, 255 ), 'scalar' ),
			'first_name' => array( 'format' => 'string', 'length' => array( 1, 32 ), 'scalar' ),
			'last_name'  => array( 'format' => 'string', 'length' => array( 1, 32 ), 'scalar' ),
			'password'   => array( 'format' => 'string', 'length' => array( 12, PHP_INT_MAX ), 'scalar' )
		);

		$this->optional = array(
			'org_id' => array( 'format' => 'MongoDB::ObjectId', 'default' => null, 'length' )
		);

		$data = $this->validate();

		//TODO: extra PW validation
		//$this->_validatePassword( $data['password'] );

		$user = new \Models\User( $data );
		$user->algorithm  = 'argon2id';
		$user->client_key = Configuration::load()->generateClientKey();
		$user->created    = $user->modified = new \MongoDB\BSON\UTCDateTime();
		$user->is_enabled = true;
		$user->hash       = password_hash( $data['password'], PASSWORD_ARGON2ID );
		$user->last_login = null;
		$user->username   = $data['email'];

		if( $creator ){
			$user->created_by = $creator->getID();
			$user->org_id     = $creator->org_id;
		}

		$user->validate();
		$this->_createUser( $user, $userResult );
		$user->setID( $userResult );

		if( $creator ){
			//TODO: add to org's users

			$this->response->emit(array(
				'authorization' => null,
				'user' => array(
					'client_key' => $user->client_key
				)
			), 201 );
		}
		else{
			$session = \Models\Session::createForUser( $user )->validate();
			//TODO: $this->_createSession( $session, $sessResult );
			$sessResult = $this->selectCollection( 'sessions' )->insertOne( $session );
			if( !self::insertedOne( $sessResult ) ){
				\Log::warning( $sessResult );
			}

			$this->response->emit(array(
				'authorization' => \Models\Session::view( $session ),
				'user' => array(
					'client_key' => $user->client_key
				)
			), 201 );
		}
	}

	private final function _createUser( \Models\User $user, &$userResult ){
		try{
			$userResult = $this->selectCollection( 'users' )->insertOne( $user );
			\Log::debug( "User created: {$user->username}" );
		}
		catch( \MongoDB\Driver\Exception\BulkWriteException $ex ){
			if( $ex->getCode() === 11000 ){
				\Log::warning( $ex->getMessage() );
				throw new Exception( "User already exists", 409 );
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

		if( self::insertedOne( $userResult ) )
			return null;
		else
			throw new Exception( 'Internal Server Error', 500 );
	}
}
