<?php

use CeusMedia\HydrogenFramework\Logic;

abstract class Logic_Import_Connector_Abstract extends Logic
{
	protected Model_Import_Connection $modelConnection;

	protected ?Entity_Import_Connection $connection	= NULL;

	protected ?object $options		= NULL;

	protected int $limit			= 0;

	protected int $offset			= 0;

	/**
	 *	@param		array		$conditions
	 *	@param		array		$orders
	 *	@param		array		$limit
	 *	@return		array<Entity_Import_SourceItem|object>
	 */
	abstract public function find( array $conditions, array $orders = [], array $limit = [] ): array;

	abstract public function getFolders( bool $recursive = FALSE ): array;

	/**
	 *	@return		Entity_Import_Connection|NULL
	 */
	public function getConnection(): ?Entity_Import_Connection
	{
		return $this->connection;
	}

	/**
	 *	@param		Entity_Import_Connection	$connection
	 *	@return		static
	 */
	public function setConnection( Entity_Import_Connection $connection  ): static
	{
		$this->connection	= $connection;
		return $this;
	}

	/**
	 *	@param		int|string		$connectionId
	 *	@return		static
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setConnectionId( int|string $connectionId  ): static
	{
		$this->connection	= $this->modelConnection->get( $connectionId );
		return $this;
	}

	/**
	 *	@param		int		$limit
	 *	@return		static
	 */
	public function setLimit( int $limit ): static
	{
		$this->limit	= $limit;
		return $this;
	}

	/**
	 *	@param		object		$options
	 *	@return		static
	 */
	public function setOptions( object $options ): static
	{
		$this->options	= $options;
		return $this;
	}

	/**
	 *	@param		int		$offset
	 *	@return		static
	 */
	public function setOffset( int $offset ): static
	{
		$this->offset	= $offset;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
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
	 *	@return		Entity_Import_SourceItem
	 */
	protected function getEmptySourceItem( string $id, string $type, array $conditions, array $orders, array $limit ): Entity_Import_SourceItem
	{
		return Entity_Import_SourceItem::fromArray( [
			'data'		=> [],
			'source'	=> Entity_Import_Source::fromArray( [
				'id'		=> $id,
				'type'		=> $type,
				'search'	=> Entity_Import_Search::fromArray( [
					'conditions'	=> $conditions,
					'orders'		=> $orders,
					'limit'			=> $limit,
				] )
			] ),
		] );
	}
}
