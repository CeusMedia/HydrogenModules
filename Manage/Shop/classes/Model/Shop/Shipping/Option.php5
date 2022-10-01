<?php
/**
 *	Data Model of Shipping Options.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Shipping Options.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */
final class Model_Shop_Shipping_Option extends Model
{
	protected $name		= 'shop_shipping_options';

	protected $columns	= array(
		'optionId',
		'title',
		'price',
	);

	protected $primaryKey	= 'optionId';

	protected array $indices		= array(
		'price',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
