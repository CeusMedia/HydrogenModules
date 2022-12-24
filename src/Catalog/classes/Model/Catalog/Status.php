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
	protected string $name			= 'catalog_states';

	protected array $columns		= [
		'statusId',
		'title',
		'available',
		'rank',
	];

	protected string $primaryKey	= 'statusId';

	protected array $indices		= [
		"available",
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
