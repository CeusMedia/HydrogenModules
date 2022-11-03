<?php
/**
 *	Data Model of Route.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			...
 *	@version		...
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Route.
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@since			...
 *	@version		...
 */
class Model_Route extends Model
{
	protected string $name		= 'routes';

	protected array $columns	= array(
		'routeId',
		'status',
		'methods',
		'ajax',
		'regex',
		'code',
		'source',
		'target',
		'title',
		'createdAt',
	);

	protected string $primaryKey	= 'routeId';

	protected array $indices		= array(
		'status',
		'regex',
		'ajax',
		'code',
		'source',
		'target',
	);

	protected int $fetchMode	= PDO::FETCH_OBJ;
}
