<?php

namespace Formatters;

final class JSON extends \Formatter{
	//From base:
	//  $this->_cacheContent
	//  $this->_cacheSource

	public final function emit( &$content ){
		$contentHeaders = $this->getHeaders( $content );
		foreach( $contentHeaders as $k => $v ){
			header( is_numeric( $k ) ? $v : "{$k}: {$v}" );
		}
		print( $this->_cacheContent );
	}

	public final function format( &$content, $isCached=true ){
		if( $isCached && $this->_cacheSource === $content )
			return $this->_cacheContent;


		if( $content instanceof \Exception )
			$formatted = self::_formatException( $content );
		else
			$formatted = self::_formatData( $content );

		if( $isCached ){
			$this->_cacheContent = $formatted;
			$this->_cacheSource = $content;
		}

		return $formatted;
	}

	public final function getChunksFooter(){
		return "]";
	}

	public final function getChunksHeader(){
		return "[";
	}

	public final function getChunksSeparator(){
		return ",";
	}

	public final function getHeaders( &$content, $isCached=true ){
		$formatted = $this->format( $content, $isCached );

		$contentHeaders[] = 'Content-Type: application/json';
		$contentHeaders[] = 'Content-Length: '. strlen( $formatted );
		return $contentHeaders;
	}

	private static final function _formatData( &$content ){
		$config = \Configuration::Load();
		if( !empty( $config->isDeveloper ) )
			return json_encode( $content, JSON_PRETTY_PRINT );
		else
			return json_encode( $content );
	}

	private static final function _formatException( \Exception &$exception ){
		return json_encode( array( 'code' => $exception->getCode(), 'message' => $exception->getMessage() ) );
	}
}
