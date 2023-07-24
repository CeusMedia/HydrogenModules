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

	protected string $name			= 'shop_specials';

	protected array $columns		= [
		"shopSpecialId",
		"creatorId",
		"bridgeId",
		"articleId",
		"title",
		"styleRules",
		"styleFiles",
		"createdAt",
		"modifiedAt",
	];

	protected string $primaryKey	= 'shopSpecialId';

	protected array $indices		= [
		"creatorId",
		"bridgeId",
		"articleId",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
