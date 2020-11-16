<?php

final class history extends Route {
	protected $formats = array(
		'application/json'
	);

	public final function __construct( Request $request ){
		parent::__construct( $request );
	}

	public final function GET(){
		dump( $_SERVER );
	}
}
