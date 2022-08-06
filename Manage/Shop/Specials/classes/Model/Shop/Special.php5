<?php
/**
 *	Data Model of Shop Specials.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
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

	protected $name		= 'shop_specials';

	protected $columns	= array(
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

	protected $primaryKey	= 'shopSpecialId';

	protected $indices		= array(
		"creatorId",
		"bridgeId",
		"articleId",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
