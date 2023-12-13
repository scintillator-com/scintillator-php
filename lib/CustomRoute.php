<?php

/*
'/get_test' => new \CustomRoute([
	[ 'GET',    ['\Controllers\Generator','GET']],
	[ 'POST',   ['\Controllers\History',  'GET'], $headers=[] ],
	[ 'DELETE', ['\Controllers\Project',  'GET'], $headers=[], $origin=null ]
]),
'/get_test' => new \CustomRoute(array(
	array( 'GET',    array('\Controllers\Generator','GET'), $headers=array(), $origin=null ),
	array( 'POST',   array('\Controllers\History',  'GET'),   $headers=array(), $origin=null ),
	array( 'DELETE', array('\Controllers\Project',  'GET'),   $headers=array(), $origin=null )
))
*/

final class CustomRoute extends \Route{
	private $handlers = array();
	
	public function __construct( $arg1 ){
		$handlers = null;
		foreach( func_get_args() as $arg ){
			if( $arg instanceof \CustomRoute ){
				$handlers = $arg->handlers;
			}
			else if( $arg instanceof \Request ){
				parent::__construct( $arg );
			}
			else if( is_array( $arg ) ){
				$this->handlers = $arg;
			}
		}

		if( $this->request ){
			foreach( $handlers as $handler ){
				$invoker = function() use( $handler ){
					list( $class, $meth ) = $handler[1];
					$controller = new $class( $this->request );
					return $controller->{$meth}();
				};
				
				//$corsHeaders = empty($handler[2]) ? null : $handler[2];
				//$corsOrigin  = empty($handler[3]) ? null : $handler[3];
				$this->setHandler( $handler[0], $invoker, $handler[2] ?? null, $handler[3] ?? null );
			}
		}
	}
}
