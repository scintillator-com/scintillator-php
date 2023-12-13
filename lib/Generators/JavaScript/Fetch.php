<?php

namespace Generators\JavaScript;

class Fetch extends \Generators\JavaScript{
	public function format( &$moment ){
		$queryArgs = $this->getQueryArgs( $moment );
		$url = $this->getURL( $moment, $queryArgs );

		$headers = $this->getHeaders( $moment );
		$bodyArgs = $this->getBodyArgs( $moment );


		$init = new \stdClass();
		$init->method = strtoupper( $moment->request->method );
		if( !empty( $headers ) ){
			foreach( $headers as &$kvp ){
				$init->headers[ $kvp['k'] ] = $kvp['v'];
			}
		}

		if( !empty( $bodyArgs ) ){
			throw new \Exception( 'Not implemented', 501 );
			$init->body = $bodyArgs;
		}


		if( !empty( $this->snippet->generator->version ) ){
			$version = $this->snippet->generator->version;
		}
		else{
			$version = 'async';
		}

		switch( $version ){
			case 'async':
				return $this->formatAsync( $init, $url );

			case 'promise':
				return $this->formatPromise( $init, $url );

			default:
				throw new \Exception( "Not implemented: '{$this->snippet->generator->version}'", 501 );
		}
	}

	private function formatAsync( $init, $url ){
		$writer = new \MemoryWriter();
		$writer->indent()->indent();
		$this->formatJSON( $init, 0, 2, $writer );
		$writer->writeLine();

		$init = 'const init = '. ltrim( "{$writer}" );
		$url = json_encode( $url, JSON_UNESCAPED_SLASHES );

		ob_start();
		require( LIB . DS .'Views'. DS .'Snippets'. DS .'js-fetch-async.php' );
		return ob_get_clean();
	}

	private function formatPromise( $init, $url ){
		$writer = new \MemoryWriter();
		$writer->indent();
		$this->formatJSON( $init, 0, 2, $writer );
		$writer->writeLine();

		$init = 'const init = '. ltrim( "{$writer}" );
		$url = json_encode( $url, JSON_UNESCAPED_SLASHES );

		ob_start();
		require( LIB . DS .'Views'. DS .'Snippets'. DS .'js-fetch-promise.php' );
		return ob_get_clean();
	}
}
