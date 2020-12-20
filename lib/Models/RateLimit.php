<?php

namespace Models;

class RateLimit extends MongoModel{
	//TODO: unique name
	//TODO: client_key unique

	public final function validate(){
		$required = array(
			org_id,
			org_client_key,
			limit
		);

		$optional = array(
		);

		//$org['admins']   = array();  //new MongoDB\BSON\ObjectId()
		//$org['users']    = array();  //new MongoDB\BSON\ObjectId()

		$data = (array)$this;
		$remainder = Validator::validate( $data, $required, $optional );
	}
}
