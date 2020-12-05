<?php

abstract class Snippet_Formatter{
	protected $snippet = null;

	protected function __construct( &$snippet ){
		$this->snippet = $snippet;
	}
	
	public static function create( &$snippet ){
		if( $snippet->formatter->language === 'javascript' && $snippet->formatter->library === 'fetch' ){
			return new Snippet_Formatter_JS_Fetch( $snippet );
		}
		else{
			throw new Exception( "Not Implemented: {$snippet->formatter->language}-{$snippet->formatter->library}" );
		}
	}

	abstract public function format( &$moment );

	protected function getHeaders( &$moment ){
		$headers = array();
		if( !empty( $this->snippet->config->header_params ) ){
			$findHeaders = array_map( 'strtolower', (array)$this->snippet->config->header_params );
			foreach( $moment->request->headers as &$header ){
				$k = array_search( strtolower( $header->k ), $findHeaders, true );
				if( $k !== false ){
					unset( $findHeaders[$k] );
					$headers[] = $header;
				}
			}
		}

		return $headers;
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

		if( !empty( $this->snippet->config->query_params ) ){
			$params = array();
			$findParams = array_map( 'strtolower', (array)$this->snippet->config->query_params );
			foreach( $moment->request->query_data as &$kvp ){
				$k = array_search( strtolower( $kvp->k ), $findParams, true );
				if( $k !== false ){
					unset( $findParams[$k] );
					$params[ $kvp->k ] = $kvp->v;
				}
			}

			$url .= '?'. http_build_query( $params );
		}

		return $url;
	}
}
