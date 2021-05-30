<?php
final class ISO8601 extends Format{
	public function __construct(){
		parent::__construct( 'number array' );
	}

	public function configure( $attributes ){
		$this->enum = \Format::get( array( "format" => "enum", "enum" => array(1) ) );
		$this->numeric = \Format::get( array( "format" => "numeric" ) );
		return $this;
	}

	public function format( $self, $value ){
		list( $date, $time ) = explode( 'T', $value, 2 );

		$tz = '-0000';
		if( substr( $time, -1 ) == 'Z' ){
			list( $time ) = explode( 'Z', $time );
		}else if( strpos( $time, '-' ) !== false ){
			list( $time, $tz ) = explode( '-', $time );
			$tz = "-{$tz}";
		}else if( strpos( $time, '+' ) !== false ){
			list( $time, $tz ) = explode( '+', $time );
			$tz = "+{$tz}";
		}

		$date = str_replace( '-', '', $date );
		$dn = strlen( $date );
		$year = (int)substr( $date, 0, 4 );
		$month = $dn >= 6 ? (int)substr( $date, 4, 2 ) : 1;
		$day = $dn == 8 ? (int)substr( $date, 6, 2 ) : 1;

		$time = str_replace( ':', '', $time );
		$tn = strlen( $time );
		$hour = (int)substr( $time, 0, 2 );
		$minute = $tn >= 4 ? (int)substr( $time, 2, 2 ) : 0;
		$second = $tn == 6 ? (int)substr( $time, 4, 2 ) : 0;

		$tz = str_replace( ':', '', $tz );
		$hr = substr( $tz, 1, 2 );
		$mn = substr( $tz, 3, 2 );
		if( $tz[ 0 ] == '-' ){
			$hour += (int)$hr;
			$minute += (int)$mn;
		}else{
			$hour -= (int)$hr;
			$minute -= (int)$mn;
		}

		$dt = new \DateTime();
		$dt->setDate( $year, $month, $day );
		$dt->setTime( $hour, $minute, $second );

		$uts = $dt->getTimestamp();
		return $uts;
	}

	public function formatArray( $values ){
		$newValues = array();
		foreach( $values as &$v ){
			$newValues[] = $this->formatScalar( $v );
		}
		return $newValues;
	}

	public function formatScalar( $value ){
		return $this->format( $this, $value );
	}

	public function isValid( $self, $value ){
		$hasT = strpos( $value, 'T' );
		if( !$hasT )
			return false;

		list( $date, $time ) = explode( 'T', $value, 2 );
		$hasColons = strpos( $time, ':' ) !== false;
		$hasDashes = strpos( $date, '-' ) !== false;
		if( $hasColons != $hasDashes )
			return false;

		return true;
	}

	public function isValidArray( $values ){
		if( empty( $values ) )
			return false;

		foreach( $values as &$v ){
			if( !$this->isValidScalar( $v ) )
				return false;
		}

		return true;
	}

	public function isValidScalar( $value ){
		return $this->isValid( $this, $value );
	}

	public function throwValidationError( $param, $value ){
		throw new \Exception( "The '{$param}' parameter must be formatted as a(n) ISO8601 date-time.", 422 );
	}
}
