<?php
/**
 *	Data model of bookstore states.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of bookstore states.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Bookstore_Status extends Model
{
	protected string $name		= 'catalog_bookstore_states';

	protected array $columns	= array(
		'statusId',
		'title',
		'available',
		'rank',
	);

	protected string $primaryKey	= 'statusId';

	protected array $indices		= array(
		"available",
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
