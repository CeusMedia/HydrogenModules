<?php
/**
 *	Data model of store states.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data model of store states.
 *	@category		cmProjects
 *	@package		LUV.Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Catalog_Status extends Model
{
	protected $name		= 'catalog_states';

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
