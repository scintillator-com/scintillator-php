<?php

namespace Models;

//defaultDuration
//maxDuration
//sunset

class Session extends MongoModel{
	//createIndex( $keys = array( "created" => 1 ), $options = array( "name": "created", "sparse": true ) );
	//createIndex( $keys = array( "expires" => 1 ), $options = array( "name": "expires", "sparse": true ) );
	//createIndex( $keys = array( "org_id"  => 1 ), $options = array( "name": "org_id",  "sparse": true ) );
	//createIndex( $keys = array( "token"   => 1 ), $options = array( "name": "token",   "sparse": true, "unique": true ) );
	//createIndex( $keys = array( "user_id" => 1 ), $options = array( "name": "user_id", "sparse": true ) );

	//BASE:
	//protected $_id;

	public $created;
	public $is_enabled;
	public $expires;
	public $org_id;
	public $token;
	public $user_id;

	public final static function createForUser( $user ){
		$session = new \Models\Session(array(
			'created' => new \MongoDB\BSON\UTCDateTime(),
			'is_enabled' => true,
			'org_id'  => $user->org_id,
			'token'   => bin2hex( random_bytes( 24 ) ),
			'user_id' => $user->getID()
		));

		$config = \Configuration::Load();
		if( $user->org_id ){
			$session->expires = new \MongoDB\BSON\UTCDateTime(( time() + $config->session['duration_default'] ) * 1000 );
		}
		else{
			$session->expires = new \MongoDB\BSON\UTCDateTime(( time() + $config->session['duration_short'] ) * 1000 );
		}
		
		return $session;
	}

	public final static function findByToken( $token ){
		return array( 'token' => $token );
	}

	public final static function findByUser( $user ){
		$query = array(
			'user_id' => $user->getID(),
			'created' => array(
				'$lt' => new \MongoDB\BSON\UTCDateTime()
			)
		);

		$config = \Configuration::Load();
		if( $user->org_id ){
			//expires soon
			$query['expires'] = array(
				'$gt' => new \MongoDB\BSON\UTCDateTime(( time() + $config->session['sunset'] ) * 1000 )
			);
		}
		else{
			//expires any time in the future
			$query['expires'] = array(
				'$gt' => new \MongoDB\BSON\UTCDateTime()
			);
		}

		return $query;
	}

	public final function getID(){
		return $this->_id;
	}

	public final function isExpired(){
		return $this->expires->toDateTime()->getTimestamp() < time();
	}

	public final function isStarted(){
		return $this->created->toDateTime()->getTimestamp() <= time();
	}

	public final function isValidDuration(){
		//\Log::info( $this->created->toDateTime()->format( 'U.u' ) ); //s.us
		//\Log::info( $this->created->toDateTime()->format( 'U.v' ) ); //s.ms
		$duration = $this->expires->toDateTime()->format( 'U' ) - $this->created->toDateTime()->format( 'U' );
		if( $duration <= 0 )
			return false;

		$config = \Configuration::Load();
		return $duration < $config->session['duration_max'];
	}

	public final function validate(){
		static $required = array(
			'created' => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
			'is_enabled' => array( 'format' => 'boolean', 'scalar' ),
			'expires' => array( 'format' => 'MongoDB::UTCDateTime', 'scalar' ),
			'token'   => array( 'format' => 'hex', 'length' => array( 1, 255 ), 'scalar' ),
			'user_id' => array( 'format' => 'MongoDB::ObjectId', 'scalar' )
		);

		static $optional = array(
			'_id'    => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'org_id' => array( 'format' => 'MongoDB::ObjectId', 'scalar' )
		);

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		if( !$this->isValidDuration() )
			throw new Exception( 'Session Invalid', 401 );

		return $this;
	}

	public final static function view( $session ){
		return array(
			'expires' => unix2iso8601( $session->expires->toDateTime()->getTimestamp() ),
			'token'   => $session->token,
			'type'    => 'bearer'
		);
	}
}
