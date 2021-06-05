<?php

namespace Generators\JavaScript;

class Axios extends \Generators\JavaScript{
	private static $HAS_BODY_METHODS = array( 'POST', 'PUT', 'PATCH' );
	private static $NO_BODY_METHODS = array( 'GET', 'DELETE', 'HEAD', 'OPTIONS' );

	public function format( &$moment ){
		$method = strtolower( $moment->request->method );

		$queryArgs = $this->getQueryArgs( $moment );
		$url = $this->getURL( $moment, $queryArgs );

		$config = new \stdClass();
		$headers = $this->getHeaders( $moment );

		$data = new \stdClass();
		$bodyArgs = $this->getBodyArgs( $moment );
		if( $this->allowBody( $moment ) ){
			if( $this->isSpecific( $moment ) ){
				//url: OK

				//data
				if( !empty( $bodyArgs ) ){
					if( $moment->request->content_type === 'application/json' ){
						throw new \Exception( 'Not implemented', 501 );
						$data = $bodyArgs;
					}
					else{
						throw new \Exception( 'Not implemented', 501 );
					}
				}

				//config headers
				if( !empty( $headers ) ){
					foreach( $headers as &$kvp ){
						$config['headers'][ $kvp['k'] ] = $kvp['v'];
					}
				}
			}
			else{
				//config url, headers, data
				$config->url = $url;
				$config->method = $method;

				if( !empty( $headers ) ){
					foreach( $headers as &$kvp ){
						$config->headers[ $kvp['k'] ] = $kvp['v'];
					}
				}

				if( !empty( $bodyArgs ) ){
					throw new \Exception( 'Not implemented', 501 );
					$config->data = $bodyArgs;
				}
			}
		}
		else{
			//url: OK
			//config headers, data
			if( !empty( $headers ) || !empty( $bodyArgs ) ){
				foreach( $headers as &$kvp ){
					$config->headers[ $kvp['k'] ] = $kvp['v'];
				}

				if( !empty( $bodyArgs ) ){
					throw new \Exception( 'Not implemented', 501 );
					$config->data = $bodyArgs;
				}
			}
		}
		
		
		switch( $method ){
			case 'get':
			case 'delete':
			case 'head':
			case 'options':
				$line = "axios.{$method}( ". json_encode( $url, JSON_UNESCAPED_SLASHES ) .", config )". PHP_EOL;
				break;

			case 'post':
			case 'put':
			case 'patch':
				$line = "axios.{$method}( ". json_encode( $url, JSON_UNESCAPED_SLASHES ) .", data, config )". PHP_EOL;
				break;

			default:
				$line = "axios.request( config )". PHP_EOL;
				break;
		}

		if( !empty( $this->snippet->generator->version ) ){
			$version = $this->snippet->generator->version;
		}
		else{
			$version = 'async';
		}

		switch( $version ){
			case 'async':
				return $this->formatAsync( $config, $data, $line );

			case 'promise':
				return $this->formatPromise( $config, $data, $line );

			default:
				throw new \Exception( "Not implemented: '{$this->snippet->generator->version}'", 501 );
		}
	}

	private function allowBody( &$moment ){
		if( in_array( $moment->request->method, self::$NO_BODY_METHODS, true ) ){
			return false;
		}
		else{
			return true;
		}
	}
	
	private function formatAsync( $config, $data, &$line ){
		$writer = new \MemoryWriter();
		$writer->indent()->indent();
		$this->formatJSON( $config, 0, 2, $writer );
		$writer->writeLine();

		$config = 'const config = '. ltrim( "{$writer}" );
		

		ob_start();
		require( LIB . DS .'Views'. DS .'Snippets'. DS .'js-axios-async.php' );
		return ob_get_clean();
	}

	private function formatPromise( $config, &$data, &$line ){
		$writer = new \MemoryWriter();
		$writer->indent();
		$this->formatJSON( $config, 0, 2, $writer );
		$writer->writeLine();

		$config = 'const config = '. ltrim( "{$writer}" );

		ob_start();
		require( LIB . DS .'Views'. DS .'Snippets'. DS .'js-axios-promise.php' );
		return ob_get_clean();
	}

	private function isSpecific( &$moment ){
		if( in_array( $moment->request->method, self::$HAS_BODY_METHODS, true ) ){
			return true;
		}
		else if( in_array( $moment->request->method, self::$NO_BODY_METHODS, true ) ){
			return true;
		}
		else{
			return true;
		}
	}
}
