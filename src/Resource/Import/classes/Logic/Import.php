<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Import extends Logic
{
	protected Model_Import_Connection $modelConnection;

	protected Model_Import_Connector $modelConnector;

	protected array $connections		= [];

	public function getConnectionInstanceFromId( $connectionId )
	{
		if( !isset( $this->connections[$connectionId] ) ){
			$connection	= $this->modelConnection->get( $connectionId );
			if( !$connection )
				throw new RangeException( 'Invalid connection ID' );
			$connector	= $this->modelConnector->get( $connection->importConnectorId  );
			if( (int) $connector->status !== Model_Import_Connector::STATUS_ENABLED )
				throw new RuntimeException( 'Connector "'.$connector->title.'" is not enabled' );

			$factory	= new ObjectFactory();
			$instance	= $factory->create( $connector->className, [$this->env] );
			$instance->setConnection( $connection );
			$this->connections[$connectionId]	= $instance->connect();
		}
		return $this->connections[$connectionId];
	}

	protected function __onInit(): void
	{
		$this->modelConnection	= new Model_Import_Connection( $this->env );
		$this->modelConnector	= new Model_Import_Connector( $this->env );
	}
}
