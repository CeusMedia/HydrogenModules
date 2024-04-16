<?php

use CeusMedia\HydrogenFramework\Logic;

abstract class Logic_Import_Connector_Abstract extends Logic
{
	protected Model_Import_Connection $modelConnection;

	protected ?object $connection	= NULL;

	protected array $options		= [];

	protected int $limit			= 0;

	protected int $offset			= 0;

	abstract public function find( array $conditions, array $orders = [], array $limit = [] ): array;

	abstract public function getFolders( bool $recursive = FALSE ): array;

	/**
	 *	@return		object|NULL
	 */
	public function getConnection(): ?object
	{
		return $this->connection;
	}

	/**
	 *	@param		object		$connection
	 *	@return		self
	 */
	public function setConnection( object $connection  ): self
	{
		$this->connection	= $connection;
		return $this;
	}

	/**
	 *	@param		int|string		$connectionId
	 *	@return		self
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setConnectionId( int|string $connectionId  ): self
	{
		$this->connection	= $this->modelConnection->get( $connectionId );
		return $this;
	}

	/**
	 *	@param		int		$limit
	 *	@return		self
	 */
	public function setLimit( int $limit ): self
	{
		$this->limit	= $limit;
		return $this;
	}

	/**
	 *	@param		array		$options
	 *	@return		self
	 */
	public function setOptions( array $options ): self
	{
		$this->options	= $options;
		return $this;
	}

	/**
	 *	@param		int		$offset
	 *	@return		self
	 */
	public function setOffset( int $offset ): self
	{
		$this->offset	= $offset;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->modelConnection	= new Model_Import_Connection( $this->env );
	}

	/**
	 *	@param		string		$id
	 *	@param		string		$type
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limit
	 *	@return		object{source: object{id: string, type: string, search: object{conditions: array, orders: array, limit: array}}, data: array}
	 */
	protected function getEmptySourceItem( string $id, string $type, array $conditions, array $orders, array $limit ): object
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
