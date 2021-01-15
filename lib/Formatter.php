<?php
abstract class Formatter{
	protected $_cache;

	public function clearCache(){
		$this->_cache = null;
		return $this;
	}

	public abstract function emit( &$content );
	public abstract function format( &$content, $isCached=true );
	//public abstract function formatData( &$content );
	//public abstract function formatException( Exception &$exception );
	public abstract function getChunksFooter();
	public abstract function getChunksHeader();
	public abstract function getChunksSeparator();
	public abstract function getHeaders( &$content, $isCached=true );
}
