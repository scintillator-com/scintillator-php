<?php
abstract class Formatter{
	protected $_cacheContent;
	protected $_cacheSource;

	public function clearCache(){
		$this->_cacheContent = null;
		$this->_cacheSource = null;
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
