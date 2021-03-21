<?php

namespace Formatters;

//Assume JS + Fetch means browser
//TODO: semicolons: true/false = $c = ';' || ''
//TODO: spacing:    $op = '(', $cp = ')', $ob = '{', $cb = '}'
//TODO: quotes:     $q = "'" || $q = '"'
abstract class JavaScript extends SnippetFormatter{
	protected function formatJSON( $value, $depth=0, $maxDepth=1, $writer=null ){
		if(empty( $value ) || is_scalar( $value ) || $depth === $maxDepth){
			$writer->write( json_encode( $value ) );
			return;
		}


		if( !$writer )
			$writer = new \MemoryWriter();

		//\Log::info( gettype( $value ) .': '. json_encode( $value ) );
		if( is_numeric_array( $value ) ){
			throw new \Exception( 'Not implemented', 501 );
			$writer->writeLine( '[' )->indent();
			$writer->outdent()->writeLine( ']' );
		}
		else{
			if( is_object( $value ) )
				$value = (array)$value;

			$i = 0;
			$n = count( $value );
			$writer->writeLine( '{' )->indent();
			foreach( $value as $k => $v ){
				++$i;
				$writer->write( json_encode( $k ) .': ' );
				$this->formatJSON( $v, $depth+1, $maxDepth, $writer );

				if( $i < $n )
					$writer->writeLine( ',' );
				else
					$writer->writeLine();
			}

			$writer->outdent()->write( '}' );
		}

		return "{$writer}";
	}
}
