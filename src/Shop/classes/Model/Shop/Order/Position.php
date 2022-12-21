<?php
/**
 *	Data Model of Order Positions.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Order Positions.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
class Model_Shop_Order_Position extends Model
{
	const STATUS_NEW		= 0;
	const STATUS_ORDERED	= 1;
	const STATUS_DELIVERED	= 2;

	const STATUSES			= [
		self::STATUS_NEW,
		self::STATUS_ORDERED,
		self::STATUS_DELIVERED,
	];

	protected string $name		= 'shop_order_positions';

	protected array $columns	= array(
		"positionId",
		"orderId",
		"userId",
		"bridgeId",
		"articleId",
		"quantity",
		"currency",
		"price",
		"priceTaxed",
		"status",
		"createdAt",
		"modifiedAt",
	);

	protected string $primaryKey	= 'positionId';

	protected array $indices		= array(
		"orderId",
		"userId",
		"bridgeId",
		"articleId",
		"currency",
		"status"
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
