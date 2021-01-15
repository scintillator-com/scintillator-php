<?php

class HTTP_MultipartBody extends HTTP_Body{
	private $_attachments = array();
	private $_boundary = null;
	private $_hasFiles = false;

	public function __construct( $attachments, $boundary ){
		$this->_attachments = $attachments;
		$this->_boundary = $boundary;
	}

	public function AsArray(){
		$data = array();
		foreach( $this->_attachments as &$att ){
			$data[ $att->getName() ] = $att->getBody();
		}
		return $data;
	}

	//NOTE: these indexes are made up
	public static function loadPhp( $boundary ){
		$attachments = array();
		foreach( $_POST as $key => &$value ){
			$att = HTTP_Attachment::loadKeyValue( $key, $value );
			$att->index = count( $attachments );
			$attachments[] = $att;
		}

		$hasFiles = false;
		if( !empty( $_FILES ) ){
			$hasFiles = true;
			foreach( $_FILES as $key => &$file ){
				if( is_array( $file['name'] ) ){
					foreach( $file['name'] as $i => $_ ){
						$f = array(
							'error'    => $file['error'][$i],
							'name'     => $file['name'][$i],
							'size'     => $file['size'][$i],
							'tmp_name' => $file['tmp_name'][$i],
							'type'     => $file['type'][$i]
						);
						$att = HTTP_Attachment::loadFile( $key, $f );
						$att->index = count( $attachments );
						$attachments[] = $att;
					}
				}
				else{
					$att = HTTP_Attachment::loadFile( $key, $file );
					$att->index = count( $attachments );
					$attachments[] = $att;
				}
			}
		}

		$body = new HTTP_MultipartBody( $attachments, $boundary );
		$body->_hasFiles = $hasFiles;
		return $body;
	}

	public function serialize(){
		$data = array();
		foreach( $this->_attachments as &$att ){
			if( $key = $att->getName() ){
				if( $att->isFile() ){
					throw new Exception( 'Not implemented' );
				}
				else{
					$data[] = $att->serialize();
				}
			}
		}
		return $data;
	}

	public function __toString(){
		ob_start();
		foreach( $this->_attachments as &$att ){
			echo "--{$this->_boundary}". PHP_EOL;
			echo "{$att}";
		}
		echo "--{$this->_boundary}--". PHP_EOL;
		return ob_get_clean();
	}
}

