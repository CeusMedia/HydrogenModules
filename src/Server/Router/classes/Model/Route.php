<?php
/**
 *	Data Model of Route.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Data Model of Route.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Route extends Model
{
	protected string $name			= 'routes';

	protected array $columns		= [
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
	];

	protected string $primaryKey	= 'routeId';

	protected array $indices		= [
		'status',
		'regex',
		'ajax',
		'code',
		'source',
		'target',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
