<?php

abstract class Snippet_Formatter{
	protected $snippet = null;

	protected function __construct( &$snippet ){
		$this->snippet = $snippet;
	}
	
	public static function create( &$snippet ){
		if( $snippet->formatter->language === 'javascript' && $snippet->formatter->library === 'axios' ){
			return new \Snippet_Formatter_JS_Axios( $snippet );
		}
		else if( $snippet->formatter->language === 'javascript' && $snippet->formatter->library === 'fetch' ){
			return new \Snippet_Formatter_JS_Fetch( $snippet );
		}
		else if( $snippet->formatter->language === 'javascript' && $snippet->formatter->library === 'jquery' ){
			return new \Snippet_Formatter_JS_JQuery( $snippet );
		}
		else{
			throw new \Exception( "Not Implemented: {$snippet->formatter->language}-{$snippet->formatter->library}" );
		}
	}

	abstract public function format( &$moment );

	protected function getBodyArgs( &$moment ){
		$bodyArgs = array();
		if( !empty( $this->snippet->config->body_params ) ){
			$findParams = array_map( 'strtolower', (array)$this->snippet->config->body_params );
			
			//TODO: sort by index?
			foreach( $moment->request->body as &$kvp ){
				$k = array_search( strtolower( $kvp->k ), $findParams, true );
				if( $k !== false ){
					unset( $findParams[$k] );
					$bodyArgs[] = $kvp;
				}
			}
		}
		return $bodyArgs;
	}

	protected function getHeaders( &$moment ){
		$headers = array();
		if( !empty( $this->snippet->config->header_params ) ){
			$findHeaders = array_map( 'strtolower', (array)$this->snippet->config->header_params );
			
			//TODO: sort by index?
			foreach( $moment->request->headers as &$kvp ){
				$k = array_search( strtolower( $kvp->k ), $findHeaders, true );
				if( $k !== false ){
					unset( $findHeaders[$k] );
					$headers[] = $kvp;
				}
			}
		}

		return $headers;
	}

	protected function getQueryArgs( &$moment ){
		$queryArgs = array();
		if( !empty( $this->snippet->config->query_params ) ){
			$findParams = array_map( 'strtolower', (array)$this->snippet->config->query_params );
			
			//TODO: sort by index?
			foreach( $moment->request->query_data as &$kvp ){
				$k = array_search( strtolower( $kvp->k ), $findParams, true );
				if( $k !== false ){
					unset( $findParams[$k] );
					$queryArgs[] = $kvp;
				}
			}
		}
		return $queryArgs;
	}

	protected function getURL( &$moment ){
		$url = "{$moment->request->scheme}://{$moment->request->host}";
		if( $moment->request->scheme === 'http' && $moment->request->port !== 80 ){
			$url .= ":{$moment->request->port}";
		}
		else if( $moment->request->scheme === 'https' && $moment->request->port !== 443 ){
			$url .= ":{$moment->request->port}";
		}

		$url .= "{$moment->request->path}";

		//if( $appendQuery && $queryArgs = $this->getQueryArgs( $moment ) ){
		//	$url .= '?'. http_build_query( $queryArgs );
		//}

		return $url;
	}
}
