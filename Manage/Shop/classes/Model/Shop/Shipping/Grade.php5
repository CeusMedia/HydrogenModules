<?php
/**
 *	Data Model of Shipping Grades.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 *	@version		3.0
 */
/**
 *	Data Model of Shipping Grades.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 *	@version		3.0
 */
final class Model_Shop_Shipping_Grade extends CMF_Hydrogen_Model{

	protected $name		= 'shippinggrades';
	protected $columns	= array(
		"shippinggrade_id",
		"title",
		"quantity",
	);
	protected $primaryKey	= 'shippinggrade_id';
	protected $indices		= array(
		"quantity"
	);
	protected $fetchMode	= PDO::FETCH_OBJ;
}
?>
