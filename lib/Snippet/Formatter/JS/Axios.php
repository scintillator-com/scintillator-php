<?php
//Assume JS + Fetch means browser
//TODO: semicolons: true/false = $c = ';' || ''
//TODO: spacing:    $op = '(', $cp = ')', $ob = '{', $cb = '}'
//TODO: quotes:     $q = "'" || $q = '"'
final class Snippet_Formatter_JS_Axios extends Snippet_Formatter{
	public function format( &$moment ){
		try{
			$writer = new MemoryWriter();
			if( $this->snippet->config->method === 'async' ){
				$writer->writeLine( 'async function(){' )->indent();
			}

			$writer->writeLine( "const config = {}" );

			$headers = $this->getHeaders( $moment );
			if( $headers ){
				$writer->writeLine( "config.headers: {" )->indent();
				foreach( $headers as &$header ){
					$writer->writeLine( "'{$header->k}': '{$header->v}'," );
				}
				$writer->outdent()->writeLine( "}" );
				$writer->writeLine();
			}
			
			//$query = null;
			//if( !empty( (array)$this->snippet->config->query_params ) ){
			//	//TODO:
			//	//URLSearchParams object.
			//	//https://developer.mozilla.org/en-US/docs/Web/API/URL/searchParams
			//	throw new Exception( 'Not implemented: query_params' );
			//}
			

			$body = null;
			if( !empty( (array)$this->snippet->config->body_params ) )
				throw new Exception( 'Not implemented: body_params' );


			$url = $this->getURL( $moment, true );


			if( $body ){
				if( $moment->request->method === 'GET' ){
					$writer->writeLine( "config.params = " );
				}
				else /*if( $isJSON ) */{
					$writer->writeLine( "const data = ". JSON.stringify( body ) );
				}
				//else if( $is
			}

			$writer->writeLine()->writeLine( 'TEST TEST TEST' )->writeLine();

			if( $this->snippet->config->method === 'async' ){
				$writer->writeLine( "try{" )
					->indent();


				//https://github.com/axios/axios#request-method-aliases
				$method = strtolower( $moment->request->method );
				switch( $method ){
					case 'get':
					case 'delete':
					case 'head':
					case 'options':
						$writer->writeLine( "const response = await axios.{$method}( '{$url}', config );" );
						break;

					case 'POST':
					case 'PUT':
					case 'PATCH':
						$writer->writeLine( "const response = await axios.{$method}( '{$url}', data, config );" );
						break;

					default:
						$writer->writeLine( "config.method = '{$method}'" );
						$writer->writeLine( "config.url = '{$url}'" );

						if( $body )
							$writer->writeLine( "config.data = data" );

						$writer->writeLine( "const response = await axios.request( config );" );
						break;
				}


				$writer->writeLine( "console.log( response.body );" )
					->writeLine( "return response.body;" )
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
				if( $this->snippet->config->decode ){
					//TODO: check content type
				}

				$writer->writeLine( "fetch( '{$url}', options );" )
					->indent()
					->writeLine( ".then( response => response.text() )" )
					->writeLine( ".then( response_text => {" )
					->writeLine( "	console.log( response_text )" )
					->writeLine( "})" )
					->writeLine( ".catch( err => {" )
					->writeLine( "	console.error( err )" )
					->writeLine( "})" );
			}
			else{
				throw new Exception( "Not Implemented: method='{$this->snippet->config->method}'" );
			}

		return "{$writer}";
		}
		catch( Exception $ex ){
			ob_end_clean();
//dump( $ex ); exit;
			throw $ex;
		}
	}
}
