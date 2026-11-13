<?php

use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;

/**
 * Logical access to Model_Address with focus on using entities.
 */
class Logic_Address extends SharedLogic
{
	protected Model_Address $model;

	/**
	 *	Find addresses.
	 *	Mimics Model_Address::getAll but limits to max 100 items.
	 *	@param		array		$conditions		Column conditions
	 *	@param		array		$orders			Orders
	 *	@param		array		$limits			Offset and limit
	 *	@return		array<Entity_Address>
	 */
	public function getAll( array $conditions, array $orders = [], array $limits = [] ): array
	{
		$offset	= (int) ( $limits[0] ?? 0 );
		$limit	= min( (int) ( $limits[1] ?? 0 ), 100 );

		return $this->model->getAll( $conditions, $orders, [$offset, $limit] );
	}

	/**
	 *	Get default location address of a relation, if existing.
	 *	The other two types are billing and delivery.
	 *	@param		string|string[]	$relationType	Relation identifier(s) like 'user' or 'customer'
	 *	@param		int|string		$relationId		Related entity ID
	 *	@return		?Entity_Address
	 */
	public function getByRelation( string|array $relationType, int|string $relationId ): ?Entity_Address
	{
		return $this->getByTypeOfRelation( Model_Address::TYPE_LOCATION, $relationType, $relationId );
	}

	/**
	 *	@param		string|array<string>	$relationType
	 *	@param		int|string				$relationId
	 *	@param		array					$conditions		Additional conditions, usable for finding specific address of this relation
	 *	@param		array					$orders			Orders
	 *	@return		array<Entity_Address>
	 */
	public function getAllByRelation( string|array $relationType, int|string $relationId, array $conditions = [], array $orders = [] ): array
	{
		return $this->model->getAllByIndices( array_merge( [
			'relationType'	=> $relationType,
			'relationId'	=> $relationId,
		], $conditions ), $orders );
	}

	/**
	 *	Get billing address of a relation, if existing.
	 *	@param		string|string[]	$relationType	Relation identifier(s) like 'user' or 'customer'
	 *	@param		int|string		$relationId		Related entity ID
	 *	@return		?Entity_Address
	 */
	public function getBillingByRelation( string|array $relationType, int|string $relationId ): ?Entity_Address
	{
		return $this->getByTypeOfRelation( Model_Address::TYPE_BILLING, $relationType, $relationId );
	}

	/**
	 *	Get delivery address of a relation, if existing.
	 *	@param		string|string[]	$relationType	Relation identifier(s) like 'user' or 'customer'
	 *	@param		int|string		$relationId		Related entity ID
	 *	@return		?Entity_Address
	 */
	public function getDeliveryByRelation( string|array $relationType, int|string $relationId ): ?Entity_Address
	{
		return $this->getByTypeOfRelation( Model_Address::TYPE_DELIVERY, $relationType, $relationId );
	}

	/**
	 *	Get address of a specific type of relation, if existing.
	 *	@param		int				$type			One of Model_Address::TYPE_[LOCATION|BILLING|DELIVERY]
	 *	@param		string|string[]	$relationType	Relation identifier(s) like 'user' or 'customer'
	 *	@param		int|string		$relationId		Related entity ID
	 *	@return		?Entity_Address
	 *	@throws		RangeException	if type is invalid
	 */
	public function getByTypeOfRelation( int $type, string|array $relationType, int|string $relationId ): ?Entity_Address
	{
		if( !in_array( $type, Model_Address::TYPES, TRUE ) )
			throw new RangeException( 'Invalid type' );

		/** @var ?Entity_Address $address */
		$address	= $this->model->getByIndices( [
			'relationType'	=> $relationType,
			'relationId'	=> $relationId,
			'type'			=> $type,
		] );
		return $address;
	}

	public function remove( Entity_Address $address ): bool
	{
		return $this->model->remove( $address->addressId );
	}

	protected function __onInit(): void
	{
		$this->model	= new Model_Address( $this->env );
	}
}