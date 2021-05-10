<?php

namespace Controllers;

class Session extends \Route{
	use \Traits\Mongo;

	protected final function createSession( $user, &$session ){
		\Log::debug( "Creating new token for user" );
		$session = \Models\Session::createForUser( $user );
		$sessResult = $this->selectCollection( 'sessions' )->insertOne( $session );
		if( !self::insertedOne( $sessResult ) ){
			\Log::warning( $sessResult );
		}
		return true;
	}

	protected final function reuseSession( $user, &$session ){
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
}
