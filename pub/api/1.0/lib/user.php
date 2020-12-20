<?php

final class user extends Route{
	use \Authorized;
	use \Mongo;
	
	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors( 'POST' );
	}

	public final function POST(){
		$this->json();

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
		//$this->validatePassword( $data['password'] );

		$user = new \Models\User( $data );
		$user->algorithm  = 'argon2id';
		$user->client_key = bin2hex( random_bytes( 16 ) );
		$user->created    = $user->modified = new \MongoDB\BSON\UTCDateTime();
		$user->enabled    = true;
		$user->hash       = password_hash( $data['password'], PASSWORD_ARGON2ID );
		$user->last_login = null;
		$user->username   = $data['email'];

		if( $creator ){
			$user->created_by = $creator->getID();
			$user->org_id     = $creator->org_id;
		}

		$user->validate();
		$result = $this->_createUser( $user );
		if( $result->isAcknowledged() && $result->getInsertedCount() === 1 ){
			$user->setID( $result );

			$session = \Models\Session::create( $user )->validate();
			$result = $this->selectCollection( 'sessions' )->insertOne( $session );
			if( !( $result->isAcknowledged() && $result->getInsertedCount() === 1 ) ){
				\Log::warning( $result );
			}

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
				$this->response->emit(array(
					'authorization' => \Models\Session::view( $session ),
					'user' => array(
						'client_key' => $user->client_key
					)
				), 201 );
			}
		}
		else{
			throw new Exception( 'Internal Server Error', 500 );
		}
	}

	private final function _createUser( \Models\User $user ){
		try{
			return $this->selectCollection( 'users' )->insertOne( $user );
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
	}
}
