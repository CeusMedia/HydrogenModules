<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Data Model of Orders.
 *	@category		Model
 *	@package		Hydrogen.Module.Resource_Shop
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model\Database\Table as DatabaseTableModel;

/**
 *	Data Model of Orders.
 *	@category		Model
 *	@package		Hydrogen.Module.Resource_Shop
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Shop_Order extends DatabaseTableModel
{
	public const STATUS_REFUNDED			= -6;
	public const STATUS_COMPLAINED			= -5;
	public const STATUS_NOT_DELIVERED		= -4;
	public const STATUS_NOT_PAYED			= -3;
	public const STATUS_REVERSED			= -2;
	public const STATUS_CANCELLED			= -1;
	public const STATUS_NEW					= 0;
	public const STATUS_AUTHENTICATED		= 1;
	public const STATUS_ORDERED				= 2;
	public const STATUS_PAYED				= 3;
	public const STATUS_PARTLY_DELIVERED	= 4;
	public const STATUS_DELIVERED			= 5;
	public const STATUS_COMPLETED			= 6;

	protected string $name			= 'shop_orders';

	protected array $columns		= [
		'orderId',
		'sessionId',
		'customerId',
		'userId',
//		'customerMode',
		'options',
		'paymentMethod',
		'paymentId',
		'status',
		'currency',
		'price',
		'priceTaxed',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'orderId';

	protected array $indices		= [
		'sessionId',
		'customerId',
		'userId',
		'paymentMethod',
		'paymentId',
		'status',
		'currency'
	];

	protected int $fetchMode		= PDO::FETCH_CLASS;

	protected ?string $className	= Entity_Shop_Order::class;
}
