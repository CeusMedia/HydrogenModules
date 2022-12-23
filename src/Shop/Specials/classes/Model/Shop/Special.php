<?php
/**
 *	Data Model of Shop Specials.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Shop Specials.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
class Model_Shop_Special extends Model
{
	const STATUS_CLOSED				= -2;
	const STATUS_OUTDATED			= -1;
	const STATUS_NEW				= 0;
	const STATUS_ACTIVE				= 1;

	const STATUSES					= [
		self::STATUS_CLOSED,
		self::STATUS_OUTDATED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
	];

	protected string $name		= 'shop_specials';

	protected array $columns	= array(
		"shopSpecialId",
		"creatorId",
		"bridgeId",
		"articleId",
		"title",
		"styleRules",
		"styleFiles",
		"createdAt",
		"modifiedAt",
	);

	protected string $primaryKey	= 'shopSpecialId';

	protected array $indices		= array(
		"creatorId",
		"bridgeId",
		"articleId",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}