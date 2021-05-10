<?php

namespace Views;

class Generator {
	public final static function default( $generator ){
		return array(
			'name'     => $generator['name'],
			'label'    => $generator['label'],
			'language' => $generator['language'],
			'library'  => $generator['library']
		);
	}
}
