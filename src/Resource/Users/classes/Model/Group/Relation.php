<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	Group Right Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Group Right Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 */
class Model_Group_Relation extends Model
{
	protected string $name			= 'group_relations';

	protected array $columns		= [
		'groupRelationId',
		'groupId',
		'moduleId',
		'entityId',
		'timestamp',
	];

	protected string $primaryKey	= 'groupRelationId';

	protected array $indices		= [
		'groupId',
		'moduleId',
		'entityId',
	];

	protected int $fetchMode				= PDO::FETCH_CLASS;

	/** @var	?string		$className		Entity class to use */
	protected ?string $className				= 'Entity_Group_Relation';
}
