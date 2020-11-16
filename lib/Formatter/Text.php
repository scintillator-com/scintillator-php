<?php
final class Formatter_Text extends Formatter{
	//From base:
	//  $this->code = 200

	public final function emit( &$data, $code=null ){
		$content = $this->format( $data );
		if( 100 <= $code && $code < 600 ){
			$this->code = $code;
		}
		
		header( 'Content-Type: text/plain', true, $code );
		header( 'Content-Length: '.strlen( $content ) );
		echo $content;
	}

	public final function format( &$data ){
		if( $data instanceof Exception )
			return $this->formatException( $data );
		else
			return "{$data}";
	}

	public final function formatData( &$data ){
		return "{$data}";
	}

	public final function formatException( Exception &$exception ){
		$code = $exception->getCode();
		if( 400 <= $code && $code < 600 ){
			$this->code = $code;
			$message = $exception->getMessage();
		}
		else{
			$this->code = 500;
			$message = 'Internal Server Error';
		}

		return "{$this->code}: {$message}";
	}
}
