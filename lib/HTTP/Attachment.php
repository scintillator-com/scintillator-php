<?php

class HTTP_Attachment extends HTTP_Data{
	//inherited:
	//protected $body = '';
	//protected $_bodyBoundary = null;
	//protected $_bodyPos = -1;
	//protected $content_length = -1;
	//protected $headers = array();
	//protected $_headerIndex = array();
	//protected $query_string = '';

	public $index = -1;
	private $_file = null;
	private $_name = null;

	public function getBody(){
		return $this->body;
	}

	public function getContentType(){
		if( $header = $this->getHeader( 'CONTENT-TYPE' ) ){
			if( !empty( $header['value'] ) )
				return $header['value'];
		}
	}

	public function getName(){
		$header = $this->getHeader( 'CONTENT-DISPOSITION' );
		if( !empty( $header['parsed']['name'] ) )
			return $header['parsed']['name'];
	}

	public function isFile(){
		return !empty( $this->_file );
	}

	public static function loadFile( &$key, &$file ){
		$attachment = new HTTP_Attachment();
		$attachment->content_length = $file['size'];
		$attachment->_file = $file;

		$attachment->_headerIndex['CONTENT-DISPOSITION'] = 0;
		$attachment->headers[] = array(
            'key'   => 'Content-Disposition',
            'value' => "form-data; name=\"{$key}\"; filename=\"{$file['name']}\"",
			'index' => 0,
			'parsed' => array(
				0 => 'form-data',
				'name' => "{$key}",
				'filename' => "{$file['name']}"
			)
		);

		$attachment->_headerIndex['CONTENT-TYPE'] = 1;
		$attachment->headers[] = array(
            'key'   => 'Content-Type',
            'value' => $file['type'],
			'index' => 1
		);

		return $attachment;
	}

	public static function loadKeyValue( &$key, &$value ){
		$attachment = new HTTP_Attachment();
		$attachment->_name = $key;
		$attachment->body = $value;
		$attachment->content_length = strlen( $value );
		$attachment->_headerIndex['CONTENT-DISPOSITION'] = 0;
		$attachment->headers[] = array(
            'key'   => 'Content-Disposition',
            'value' => "form-data; name=\"{$key}\"",
			'index' => 0,
			'parsed' => array(
				0 => 'form-data',
				'name' => "{$key}"
			)
		);

		return $attachment;
	}

	public static function loadStream( &$stream, &$boundary ){
		$headers = self::readHeaderStream( $stream );
		if( empty( $headers ) )
			return null;


		//Log::info( $boundary );
		$attachment = new HTTP_Attachment();
		$attachment->_bodyBoundary = $boundary;
		$attachment->loadHeaders( $headers );

		$attachment->body = '';
		$attachment->_bodyPos = $initPos = ftell( $stream );
		while( !feof( $stream ) ){
			$line = fgets( $stream );
			//Log::info( $line );
			$pos = strpos( $line, "--{$boundary}" );
			if( $pos === 0 ){
				break;
			}
			else{
				$attachment->body .= $line;
				$initPos = ftell( $stream );
			}
		}

		if( substr_compare( $attachment->body, "\r\n", -2 ) === 0 ){
			$attachment->body = substr( $attachment->body, 0, -2 );
		}
		else if( substr_compare( $attachment->body, "\n", -1 ) ){
			$attachment->body = substr( $attachment->body, 0, -1 );
		}
		else{
			Log::warning( 'EOL not trimmed' );
		}

		$attachment->content_length = $initPos - $attachment->_bodyPos;
		fseek( $stream, $initPos );

		if( $header = $attachment->getHeader( 'CONTENT-DISPOSITION' ) ){
			$attachment->_name = $header['parsed']['name'];

			if( !empty( $header['parsed']['filename'] ) ){
				$attachment->_file = array(
					'name'     => $header['parsed']['filename'],
					'size'     => $attachment->content_length,
					'tmp_name' => null,   //implies $this->body
					'type'     => null
				);

				if( $header = $attachment->getHeader( 'CONTENT-TYPE' ) ){
					$attachment->_file['type'] = $header['value'];
				}
			}
		}

		return $attachment;
	}

	public function serialize(){
		$data = array(
			'key'   => $this->_name,
			'value' => $this->body,
			'index' => $this->index,
			'headers' => array()
		);

		if( !empty( $this->headers ) ){
			foreach( $this->headers as &$header ){
				$data['headers'][] = array(
					'key'   => $header['key'],
					'value' => $header['value'],
					'index' => $header['index']
				);
			}
		}

		return $data;
	}

	public function __toString(){
		ob_start();
		foreach( $this->headers as &$h ){
			echo "{$h['key']}: {$h['value']}". PHP_EOL;
		}

		echo PHP_EOL ."{$this->body}". PHP_EOL;
		return ob_get_clean();
	}
}

