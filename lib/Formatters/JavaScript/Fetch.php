<?php

namespace Formatters\JavaScript;

class Fetch extends \Formatters\JavaScript{
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


		if( $this->snippet->generator->version === 'async' ){
			return $this->formatAsync( $init, $url );
		}
		else if( $this->snippet->generator->version === 'promise' ){
			return $this->formatPromise( $init, $url );
		}
		else{
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
