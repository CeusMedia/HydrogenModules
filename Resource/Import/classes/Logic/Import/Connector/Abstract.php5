<?php

use CeusMedia\HydrogenFramework\Logic;

abstract class Logic_Import_Connector_Abstract extends Logic
{
	protected $modelConnection;

	protected $options			= [];

	protected $limit			= 0;

	protected $offet			= 0;

	abstract public function find( $conditions, $orders = [], $limit = [] ): array;

	abstract public function getFolders( bool $recursive = FALSE ): array;

	public function getConnection()
	{
		return $this->connection;
	}

	public function setConnection( $connection  ): self
	{
		$this->connection	= $connection;
		return $this;
	}

	public function setConnectionId( $connectionId  ): self
	{
		$this->connection	= $this->modelConnection->get( $connectionId );
		return $this;
	}

	public function setOptions( $options ): self
	{
		$this->options	= $options;
		return $this;
	}

	public function setLimit( int $limit ): self
	{
		$this->limit	= $limit;
		return $this;
	}

	public function setOffset( int $offet ): self
	{
		$this->offet	= $offet;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->modelConnection	= new Model_Import_Connection( $this->env );
	}

	protected function getEmptySourceItem( string $id, string $type, $conditions, array $orders, array $limit )
	{
		return (object) [
			'source'	=> (object) [
				'id'		=> $id,
				'type'		=> $type,
				'search'	=> (object) [
					'conditions'	=> $conditions,
					'orders'		=> $orders,
					'limit'			=> $limit,
				],
			],
			'data'		=> [],
		];
	}
}
