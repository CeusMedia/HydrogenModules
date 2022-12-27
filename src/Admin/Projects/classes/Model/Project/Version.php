<?php
/**
 *	Project Version Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	Project Version Model.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class Model_Project_Version extends Model
{
	protected string $name			= 'project_versions';

	protected array $columns		= [
		'projectVersionId',
		'projectId',
		'status',
		'version',
		'title',
		'description',
		'createdAt',
		'modifiedAt',
	];

	protected string $primaryKey	= 'projectVersionId';

	protected array $indices		= [
		'projectId',
		'status',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
