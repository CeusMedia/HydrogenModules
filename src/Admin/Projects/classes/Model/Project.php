<?php
/**
 *	Project Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Project Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class Model_Project extends Model
{
	protected string $name			= 'projects';

	protected array $columns		= [
		'projectId',
		'status',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'projectId';

	protected array $indices		= [
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
