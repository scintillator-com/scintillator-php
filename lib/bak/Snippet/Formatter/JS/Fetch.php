<?php
//Assume JS + Fetch means browser
//TODO: semicolons: true/false = $c = ';' || ''
//TODO: spacing:    $op = '(', $cp = ')', $ob = '{', $cb = '}'
//TODO: quotes:     $q = "'" || $q = '"'
final class Snippet_Formatter_JS_Fetch extends Snippet_Formatter{
	private $useBasic = true;

	//TODO: formatAsync
	//TODO: formatPromise
	public function format( &$moment ){
		try{
			$bodyArgs = $this->getBodyArgs( $moment );
			$headers = $this->getHeaders( $moment );
			$queryArgs = $this->getQueryArgs( $moment );

			$writer = new \MemoryWriter();
			if( $this->snippet->config->method === 'async' ){
				$writer->writeLine( 'async () => {' )
					->indent();
			}
			else if( $this->snippet->config->method === 'promise' ){
				//ok
			}
			else{
				throw new \Exception( "Method not implemented: '{$this->snippet->config->method}'" );
			}

			$writer->writeLine( 'const init = {' )
				->indent();

			if( $headers || $bodyArgs )
				$writer->writeLine( "method: '{$moment->request->method}'," );
			else
				$writer->writeLine( "method: '{$moment->request->method}'" );


			if( $headers ){
				$writer->write( 'headers: ' );
				$this->writeObject( $writer, $headers );

				if( $bodyArgs )
					$writer->writeLine( ',' );
				else
					$writer->writeLine();
				
			}

			//if( $queryArgs = $this->getQueryArgs( $moment ) ){
			//	$this->writeQueryObject( $writer, $headers );
			//	$initItems[] = 'queryArgs';
			//}

			if( $bodyArgs ){
				if( $moment->request->content_type === 'application/json' ){
					$writer->write( 'body: JSON.stringify(' );
					$this->writeObject( $writer, $bodyArgs );
					$writer->writeLine();
				}
				else if( $moment->request->content_type === 'application/x-www-form-urlencoded' ){
					
				}
				else if( $moment->request->content_type === 'multipart/form-data' ){
					//TODO: FormData
					$this->write( 'body: ' );
				}
			}

			$writer->outdent()
				->writeLine( '}' )
				->writeLine();

			$query = '';
			if( $queryArgs ){
				$writer->write( 'const queryData = ' );
				$this->writeObject( $writer, $queryArgs );
				$writer->writeLine()
					->writeLine();

				$writer->writeLine( 'let queryString = "?"' )
					->writeLine( 'for( let [ key, value ] of queryData.entries() ){' )
					->indent()
					->writeLine( 'queryString += encodeURIComponent( key )+"="+encodeURIComponent( value )' )
					->outdent()
					->writeLine( '}' )
					->writeLine();

				$query = '+queryString';
			}


			$url = $this->getURL( $moment );
			if( $this->snippet->config->method === 'async' ){
				$writer->writeLine( "try{" )
					->indent()
					->writeLine( "const response = await fetch('{$url}'{$query}, init)" )
					->writeLine( "const response_text = await response.text()" )
					->writeLine( "console.log( response_text )" )
					->writeLine( "return response_text" )
					->outdent()
					->writeLine( '}' )
					->writeLine( 'catch( err ){' )
					->indent()
					->writeLine( "console.error( err );" )
					->outdent()
					->writeLine( '}' )
					->outdent()
					->writeLine( '}' )
					->outdent();
			}
			else if( $this->snippet->config->method === 'promise' ){
				$writer->writeLine( "return fetch('{$url}'{$query}, init)" )
					->indent()
					->writeLine( ".then(response => {" )
					->writeLine( "	return response.text()" )
					->writeLine( "})" )
					->writeLine( ".then(esponse_text => {" )
					->writeLine( "	console.log( response_text )" )
					->writeLine( "})" )
					->writeLine( ".catch(err => {" )
					->writeLine( "	console.error( err )" )
					->writeLine( "})" );
			}
			else{
				throw new \Exception( "Not Implemented: method='{$this->snippet->config->method}'" );
			}

			return "{$writer}";
		}
		catch( Exception $ex ){
			ob_end_clean();
//dump( $ex ); exit;
			throw $ex;
		}
	}

	private function writeHeaders( MemoryWriter $writer, &$headers ){
		if( $this->useBasic ){
			//if( $this->standalone ){
			//	$writer->write( 'const headers = ' );
			//}
			
			$writer->writeLine( "{" )
				->indent();

			$last = count( $headers ) - 1;
			foreach( $headers as $i => &$header ){
				if( $i === $last )
					$writer->writeLine( "'{$header->k}': '{$header->v}'" );
				else
					$writer->writeLine( "'{$header->k}': '{$header->v}'," );
			}

			$writer->outdent()
				->write( '}' );

			//if( $this->standalone ){
			//	$writer->writeLine();
			//}
		}
		else{
			$writer->writeLine( "const headers = new Headers({" )
				->indent();

			$last = count( $headers ) - 1;
			foreach( $headers as $i => &$header ){
				if( $i === $last )
					$writer->writeLine( "'{$header->k}': '{$header->v}'" );
				else
					$writer->writeLine( "'{$header->k}': '{$header->v}'," );
			}

			$writer->outdent()
				->writeLine( '})' );
		}
	}

	private function writeObject( MemoryWriter $writer, &$data ){
		$writer->writeLine( "{" )
			->indent();

		$last = count( $data ) - 1;
		foreach( $data as $i => &$kvp ){
			//TODO: check quoting

			if( $i === $last )
				$writer->writeLine( "'{$kvp->k}': '{$kvp->v}'" );
			else
				$writer->writeLine( "'{$kvp->k}': '{$kvp->v}'," );
		}

		$writer->outdent()
			->write( '}' );
	}

	private function writeQueryObject( MemoryWriter $writer, &$queryArgs ){
		$writer->writeLine( "const queryArgs = {" )
			->indent();

		$last = count( $queryArgs ) - 1;
		foreach( $queryArgs as $i => &$qa ){
			if( $i === $last )
				$writer->writeLine( "'{$qa->k}': '{$qa->v}'" );
			else
				$writer->writeLine( "'{$qa->k}': '{$qa->v}'," );
		}

		$writer->outdent()
			->writeLine( '}' );
	}
}

/*

if( $language === 'js' ){
	if( $library === 'fetch' ){
		if( $platform === 'browser' ){
			if( $query_params ){
				if( $useBasic ){
					const queryString = ''
					for( const [ key, value ] of data ){
						queryString += 
					}
				}
				else{
					const queryString = new URLSearchParams()
					queryString.append( key, value )
					return queryString.toString()
				}
			}

			if( $header_params ){
				if( $useBasic ){
					const headers = {}
					return headers
				}
				else{
					const headers = new Headers()
					headers.append( key, value )
					return headers
				}
			}
		}
		else{
			throw new \Exception( "Unsupported platform: {$platform}" );knj 	
		}
	}
}

*/