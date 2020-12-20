<?php

final class org extends Route{
	use \Authorized;
	use \Mongo;

	public final function __construct( Request $request ){
		parent::__construct( $request );
		$this->response->cors( 'POST' );
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
		$org = new \Models\Org( $data );
		$org->client_key = bin2hex( random_bytes( 16 ) );
		$org->created    = $org->modified = new MongoDB\BSON\UTCDateTime();
		$org->created_by = $user->getID();
		$org->admins     = array( $this->session->user_id );
		$org->users      = array( $this->session->user_id );
		$org->validate();

		$result = $this->_createOrg( $org );
		if( $result->isAcknowledged() && $result->getInsertedCount() === 1 ){
			$user->org_id = $result->getInsertedId();
			$query  = array( '_id'    => $user->getID() );
			$update = array( '$set' => array( 'org_id' => $user->org_id ));
			$result = $this->selectCollection( 'users' )->updateOne($query, $update);

			$session = \Models\Session::create( $user )->validate();
			$result = $this->selectCollection( 'sessions' )->insertOne( $session );
			if( !( $result->isAcknowledged() && $result->getInsertedCount() === 1 ) ){
				\Log::warning( $result );
			}

			$this->response->emit( array(
				'authorization' => \Models\Session::view( $session ),
				'org' => array(
					'client_key' => $org->client_key
				)
			), 201);
		}
		else{
			throw new Exception( 'Internal Server Error', 500 );
		}
	}
	
	private final function _createOrg( \Models\Org $org ){
		try{
			return $this->selectCollection( 'orgs' )->insertOne( $org );
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
	}
}