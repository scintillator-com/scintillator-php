<?php

namespace Models;

class RateLimit extends MongoModel{
	//db.rate_limits.createIndex({ "org_id" :         1 }, { "name": "org_id",         "unique": true })
	//db.rate_limits.createIndex({ "org_client_key" : 1 }, { "name": "org_client_key", "unique": true })

	//BASE:
	//protected $_id;

	public $org_id;
	public $org_client_key;
	public $proxy_adhoc;
	public $proxy_evergreen;

	public static final function createForOrg( \Models\Org $org, \Models\Plan $plan ){
		$rateLimit = new RateLimit();
		$rateLimit->org_id          = $org->getID();
		$rateLimit->org_client_key  = $org->client_key;
		$rateLimit->proxy_adhoc     = 0;
		$rateLimit->proxy_evergreen = $plan->proxy_ratelimit->init;
		$rateLimit->validate();
		return $rateLimit;
	}

	public final function validate(){
		static $required = array(
			"org_id"          => array( "format" => "MongoDB::ObjectId", "scalar" ),
			"org_client_key"  => array( "format" => "base64",            "scalar" ),
			"proxy_adhoc"     => array( "format" => "integer",           "scalar" ),
			"proxy_evergreen" => array( "format" => "integer",           "scalar" )
		);

		static $optional = array();

		$data = (array)$this;
		$remainder = \Validator::validate( $data, $required, $optional );
		return true;
	}
}
