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
	protected string $name		= 'shop_shipping_grades';

	protected array $columns	= array(
		'gradeId',
		'title',
		'weight',
		'fallback',
	);

	protected string $primaryKey	= 'gradeId';

	protected array $indices		= array(
		'weight',
		'fallback',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
