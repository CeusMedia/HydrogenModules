<?php
/**
 *	Data Model of Shipping Options.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 *	@version		3.0
 */
/**
 *	Data Model of Shipping Options.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 *	@version		3.0
 */
final class Model_Shop_Shipping_Option extends CMF_Hydrogen_Model{

	protected $name		= 'shop_shipping_options';
	protected $columns	= array(
		'optionId',
		'title',
		'price',
	);
	protected $primaryKey	= 'optionId';
	protected $indices		= array(
		'price',
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
