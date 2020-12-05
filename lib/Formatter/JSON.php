<?php
final class Formatter_JSON extends Formatter{
	//From base:
	//  $this->code = 200

	public final function emit( &$data, $code=null ){
		$content = $this->format( $data );
		if( $code )
			$this->code = $code;

		header( 'Content-Type: application/json', true, $this->code );
		header( 'Content-Length: '.strlen( $content ) );
		echo $content;
	}

	public final function format( &$data ){
		if( $data instanceof Exception )
			return $this->formatException( $data );

		$config = Configuration::Load();
		if( !empty( $config->isDeveloper ) )
			return json_encode( $data, JSON_PRETTY_PRINT );

		return json_encode( $data );
	}

	public final function formatData( &$data ){
		return json_encode( $data );
	}

	public final function formatException( Exception &$exception ){
		return json_encode( array( 'code' => $exception->getCode(), 'message' => $exception->getMessage() ) );
	}
}
