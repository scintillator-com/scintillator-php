<?php

//namespace Formatters\Python;

class Requests extends \Formatters\SnippetFormatter{
	public function format( &$moment ){
		//params?
		$queryArgs = $this->getQueryArgs( $moment );
		$url = $this->getURL( $moment, $queryArgs );

		//headers
		$headers = $this->getHeaders( $moment );
		
		//data?
		$bodyArgs = $this->getBodyArgs( $moment );

		//check content-type


		$method = strtolower( $moment->request->method );
		require( LIB . DS .'Views'. DS .'Snippets'. DS .'python-requests.php' );
	}
}
