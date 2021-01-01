<?php

class Formatter_Empty extends Formatter{
	public final function emit( &$data, $code=null ){
		$contentHeaders = $this->getHeaders( $content );
		foreach( $contentHeaders as $k => $v ){
			header( is_numeric( $k ) ? $v : "{$k}: {$v}" );
		}
	}

	public final function format( &$content, $isCached=true ){
		return null;
	}

	public final function getHeaders( &$content, $isCached=true ){
		return array();
	}
}
