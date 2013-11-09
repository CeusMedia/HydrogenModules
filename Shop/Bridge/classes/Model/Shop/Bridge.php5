<?php
/**
 *	Data Model of Shop Bridges.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
/**
 *	Data Model of Shop Bridges.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Neon_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.07.2006
 *	@version		3.0
 */
class Model_Shop_Bridge extends CMF_Hydrogen_Model {

	protected $name		= 'shop_bridges';
	protected $columns	= array(
			"bridgeId",
			"class",
			"createdAt",
			);
	protected $primaryKey	= 'bridgeId';
	protected $indices		= array(
		"class",
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
