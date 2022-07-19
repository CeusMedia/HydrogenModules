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

	protected $name		= 'shop_order_positions';

	protected $columns	= array(
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
	);

	protected $primaryKey	= 'positionId';

	protected $indices		= array(
		"orderId",
		"userId",
		"bridgeId",
		"articleId",
		"status"
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
