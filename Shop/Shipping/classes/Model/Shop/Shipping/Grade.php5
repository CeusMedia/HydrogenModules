<?php
/**
 *	Data Model of Shipping Grades.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 */
/**
 *	Data Model of Shipping Grades.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 *	@since			02.08.2006
 */
final class Model_Shop_Shipping_Grade extends CMF_Hydrogen_Model
{
	protected $name		= 'shop_shipping_grades';

	protected $columns	= array(
		'gradeId',
		'title',
		'weight',
		'fallback',
	);

	protected $primaryKey	= 'gradeId';

	protected $indices		= array(
		'weight',
		'fallback',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
