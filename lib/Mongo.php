<?php

trait Mongo{
	private $db;
	private $dbName;

	protected final function getClient(){
		static $client;
		if( empty( $client ) ){
			$config = Configuration::Load();
			$client = new \MongoDB\Client( $config->mongoDB['uri'] );
		}
		return $client;
	}

	protected static final function insertedOne( \MongoDB\InsertOneResult $result ){
		return $result->isAcknowledged() && $result->getInsertedCount() === 1;
	}

	protected final function selectDB( $db ){
		return $this->selectDatabase( $db );
	}

	protected final function selectDatabase( $db ){
		static $dbs = array();
		if( empty( $dbs[ $db ] ) ){
			$dbs[ $db ] = $this->getClient()->selectDatabase( $db );
		}

		$this->db = $dbs[ $db ];
		$this->dbName = $db;
		return $dbs[ $db ];
	}

	protected final function selectCollection( $col ){
		static $collections = array();
		if( empty( $collections[ $this->dbName ][ $col ] ) ){
			if( empty( $this->db ) ){
				$config = Configuration::Load();
				$this->getClient();
				$this->selectDatabase( $config->mongoDB['database'] );
			}
			
			$collections[ $this->dbName ][ $col ] = $this->db->selectCollection( $col );
		}
		return $collections[ $this->dbName ][ $col ];
	}

	protected static final function updatedOne( \MongoDB\UpdateResult $result ){
		return $result->isAcknowledged() && $result->getModifiedCount() === 1;
	}
}
