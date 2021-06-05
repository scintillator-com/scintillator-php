<?php

abstract class SnippetGenerator{
	protected $snippet;
	
	public function __construct( &$snippet ){
		$this->snippet = $snippet;
	}

	public static function hi(){
		\Log::warning( 'hi' );
	}

	public static function create( &$snippet ){
		switch( $snippet->generator->name ){
			case 'js-axios':
				$formatter = new \Generators\JavaScript\Axios( $snippet );
				break;

			case 'js-fetch':
				$formatter = new \Generators\JavaScript\Fetch( $snippet );
				break;

			/*
			case 'js-xhr':
				$formatter = new \Generators\Python\Requests( $snippet );
				break;

			case 'php-curl':
				$formatter = new \Generators\Python\Requests( $snippet );
				break;
			*/

			case 'python-requests':
				$formatter = new \Generators\Python\Requests( $snippet );
				break;

			default:
				throw new \Exception( "SnippetGenerator not implemented: '{$snippet->generator->name}'", 501 );
		}
		
		//$formatter = new \SnippetGenerator( $snippet );
		return $formatter;
	}
	
	public abstract function format( &$moment );

	protected function getBodyArgs( &$moment ){
		$bodyArgs = array();
		if( !empty( $this->snippet->config->body_params[0] ) ){
			$findParams = array_map( 'strtolower', (array)$this->snippet->config->body_params );
			foreach( $moment->request->body as &$kvp ){
				$k = array_search( strtolower( $kvp->k ), $findParams, true );
				if( $k !== false ){
					unset( $findParams[$k] );
					$bodyArgs[] = $kvp;
				}
			}
		}
		
		//is it sortable?
		
		return $bodyArgs;
	}

	protected function getHeaders( &$moment ){
		$headers = array();
		if( !empty( $this->snippet->config->header_params[0] ) ){
			$findHeaders = array_map( 'strtolower', (array)$this->snippet->config->header_params );
			foreach( $moment->request->headers as &$kvp ){
				$k = array_search( strtolower( $kvp->k ), $findHeaders, true );
				if( $k !== false ){
					unset( $findHeaders[$k] );
					$headers[] = $kvp;
				}
			}
		}

		usort( $headers, function( &$left, &$right ){
			return $left['i'] - $right['i'];
		});

		return $headers;
	}

	protected function getQueryArgs( &$moment ){
		$queryArgs = array();
		if( !empty( $this->snippet->config->query_params[0] ) ){
			$findParams = array_map( 'strtolower', (array)$this->snippet->config->query_params );
			foreach( $moment->request->query_data as &$kvp ){
				$k = array_search( strtolower( $kvp->k ), $findParams, true );
				if( $k !== false ){
					unset( $findParams[$k] );
					$queryArgs[] = $kvp;
				}
			}
		}

		usort( $queryArgs, function( &$left, &$right ){
			return $left['i'] - $right['i'];
		});

		return $queryArgs;
	}

	protected function getURL( &$moment, &$queryArgs=null ){
		$url = "{$moment->request->scheme}://{$moment->request->host}";
		if( $moment->request->scheme === 'http' && $moment->request->port !== 80 ){
			$url .= ":{$moment->request->port}";
		}
		else if( $moment->request->scheme === 'https' && $moment->request->port !== 443 ){
			$url .= ":{$moment->request->port}";
		}

		$url .= "{$moment->request->path}";

		if( $queryArgs ){
			$args = array();
			foreach( $queryArgs as &$kvp ){
				$args[$kvp['k']] = $kvp['v'];
			}
			$url .= '?'. http_build_query( $args );
		}

		return $url;
	}
}