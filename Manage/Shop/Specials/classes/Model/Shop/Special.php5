<?php
/**
 *	Data Model of Shop Specials.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			29.06.2019
 */
/**
 *	Data Model of Shop Specials.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			29.06.2019
 */
class Model_Shop_Special extends CMF_Hydrogen_Model {

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
?>
