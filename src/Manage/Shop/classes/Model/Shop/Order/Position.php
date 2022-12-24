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

	protected string $name			= 'shop_order_positions';

	protected array $columns		= [
		"positionId",
		"orderId",
		"userId",
		"bridgeId",
		"articleId",
		"quantity",
		"price",
		"priceTaxed",
		"status",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'positionId';

	protected array $indices		= [
		"orderId",
		"userId",
		"bridgeId",
		"articleId",
		"status"
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
