<?php

use CeusMedia\Common\Alg\Obj\Factory as ObjectFactory;
use CeusMedia\Common\Exception\NotEnabled as NotEnabledException;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Import extends Logic
{
	/** @var Model_Import_Connection $modelConnection  */
	protected Model_Import_Connection $modelConnection;

	/** @var Model_Import_Connector $modelConnector  */
	protected Model_Import_Connector $modelConnector;

	/** @var Logic_Import_Connector_Interface[] $connections  */
	protected array $connections		= [];

	/**
	 *	@param		int|string		$connectionId
	 *	@return		Logic_Import_Connector_Interface
	 *	@throws		RangeException			if connection ID is invalid
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getConnectionInstanceFromId( int|string $connectionId ): Logic_Import_Connector_Interface
	{
		if( !isset( $this->connections[$connectionId] ) ){
			/** @var object $connection */
			$connection	= $this->modelConnection->get( $connectionId );
			if( !$connection )
				throw new RangeException( 'Invalid connection ID' );
			$connector	= $this->modelConnector->get( $connection->importConnectorId  );
			if( (int) $connector->status !== Model_Import_Connector::STATUS_ENABLED )
				throw new NotEnabledException( 'Connector "'.$connector->title.'" is not enabled' );

			/** @var Logic_Import_Connector_Interface $instance */
			$instance	= ObjectFactory::createObject( $connector->className, [$this->env] );
			$instance->setConnection( $connection );
			$this->connections[$connectionId]	= $instance->connect();
		}
		return $this->connections[$connectionId];
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->modelConnection	= new Model_Import_Connection( $this->env );
		$this->modelConnector	= new Model_Import_Connector( $this->env );
	}
}
