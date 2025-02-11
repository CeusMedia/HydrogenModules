<?php
/**
 *	Data Model of Order Positions.
 *	@category		Model
 *	@package		Hydrogen.Module.Shop
 *	@author			Christian Würker <Christian.Wuerker@ceus-media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Order Positions.
 *	@category		Model
 *	@package		Hydrogen.Module.Shop
 *	@author			Christian Würker <Christian.Wuerker@ceus-media.de>
 */
class Model_Shop_Order_Position extends Model
{
	public const STATUS_NEW			= 0;
	public const STATUS_ORDERED		= 1;
	public const STATUS_DELIVERED	= 2;

	public const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_ORDERED,
		self::STATUS_DELIVERED,
	];

	protected string $name			= 'shop_order_positions';

	protected array $columns		= [
		'positionId',
		'orderId',
		'userId',
		'bridgeId',
		'articleId',
		'quantity',
		'currency',
		'price',
		'priceTaxed',
		'status',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'positionId';

	protected array $indices		= [
		'orderId',
		'userId',
		'bridgeId',
		'articleId',
		'currency',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
