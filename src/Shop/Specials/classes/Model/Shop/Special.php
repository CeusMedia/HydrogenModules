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
	public const STATUS_CLOSED		= -2;
	public const STATUS_OUTDATED	= -1;
	public const STATUS_NEW			= 0;
	public const STATUS_ACTIVE		= 1;

	public const STATUSES			= [
		self::STATUS_CLOSED,
		self::STATUS_OUTDATED,
		self::STATUS_NEW,
		self::STATUS_ACTIVE,
	];

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
