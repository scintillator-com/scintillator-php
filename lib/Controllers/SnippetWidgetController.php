<?php

namespace Controllers;

final class SnippetWidgetController extends \Route{
	use \Traits\Mongo;

	public final function __construct( \Request $request ){
		parent::__construct( $request );
		$this->setHandler( 'GET', array( $this, 'GET' ) );
	}

	public final function GET(){
		$this->text();

		$this->required = array();
		$this->optional = array(
			'id'        => array( 'format' => 'MongoDB::ObjectId', 'scalar' ),
			'generator' => array( 'format' => 'string', 'scalar' ),
			'version'   => array( 'format' => 'string', 'scalar' )
		);
		try{
			$data = $this->validate( $_GET );
		}
		catch( \Exception $ex ){
			throw new \Exception( 'Not Found', 404, $ex );
			//TODO: something cute here
		}

		if( empty( $data['id'] ) )
			throw new \Exception( 'Not Found', 404 );
			//TODO: something cute here


		$snippetQuery[ '_id' ] = $data['id'];
		$snippetRes = $snippet = $this->selectCollection( 'snippets' )->findOne( $snippetQuery );
		if( !$snippetRes )
			throw new \Exception( 'Not Found', 404 );
			//TODO: something cute here


		$momentQuery['_id'] = $snippetRes->moment_id;
		$momentOptions = array(
			'projection' => array(
				'request' => 1
			)
		);
		$momentRes = $this->selectCollection( 'moments' )->findOne( $momentQuery, $momentOptions );
		if( !$momentRes )
			throw new \Exception( 'Not Found', 404 );



		if( !empty( $data['generator'] ) )
			$snippet->generator->name = $data['generator'];

		if( !empty( $data['version'] ) )
			$snippet->generator->version = $data['version'];

		$formatter = \Formatters\SnippetFormatter::create( $snippetRes );
		$code = $formatter->format( $momentRes );

		$this->html();
		require( LIB . DS .'Views'. DS .'snippet-widget.php' );
	}
}
