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
	protected $name		= 'catalog_bookstore_states';

	protected $columns	= array(
		'statusId',
		'title',
		'available',
		'rank',
	);

	protected $primaryKey	= 'statusId';

	protected $indices		= array(
		"available",
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
