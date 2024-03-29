<?php
//Assume JS + Axios means browser
//TODO: semicolons: true/false = $c = ';' || ''
//TODO: spacing:    $op = '(', $cp = ')', $ob = '{', $cb = '}'
//TODO: quotes:     $q = "'" || $q = '"'
final class Snippet_Formatter_JS_JQuery extends Snippet_Formatter{
	public function format( &$moment ){
		try{
			$writer = new \MemoryWriter();
			if( $this->snippet->config->method === 'async' ){
				$writer->writeLine( 'async function(){' )->indent();
			}
			
			$headers = $this->getHeaders( $moment );
			if( $headers ){
				$writer->writeLine( "const headers = new Headers();" );
				foreach( $headers as &$header ){
					//TODO: json_encode?
					$writer->writeLine( "headers.append( '{$header->k}', '{$header->v}' );" );
				}
				$writer->writeLine();
			}

			$body = null;
			if( !empty( $this->snippet->config->body_params ) )
				throw new \Exception( 'Not implemented: body_params' );


			$url = $this->getURL( $moment );


			$suffix = '';
			if( $headers || $body ){
				$suffix = ',';
			}

			$writer->writeLine( "const options = {" )
				->indent()
				->writeLine( "'method': '{$moment->request->method}'{$suffix}" );
			
			if( $headers ){
				$suffix = $body ? ',' : '';
				$writer->writeLine( "'headers': headers{$suffix}" );
			}

			if( $body ){
				//if JSON
				$writer->writeLine( "," )
					->write( "'body': JSON.stringify( body )" );
			}

			$writer->outdent()
				->writeLine( '};' )
				->writeLine();

			if( $this->snippet->config->method === 'async' ){
				$writer->writeLine( "try{" )
					->indent()
					->writeLine( "const response = fetch( '{$url}', options );" )
					->writeLine( "const response_text = await response.text();" )
					->writeLine( "console.log( response_text );" )
					->writeLine( "return response_text;" )
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
}
