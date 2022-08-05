<?php
/**
 *	Data Model of Shipping Grades.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Shipping Grades.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
final class Model_Shop_Shipping_Grade extends Model
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
